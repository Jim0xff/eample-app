<?php

namespace Pump\Token\Service;

use App\InternalServices\Airdrop\AirdropService;
use App\InternalServices\Coingecko\CoingeckoService;
use App\InternalServices\DomainException;
use App\InternalServices\GraphService\Service;
use App\InternalServices\LazpadTaskService\LazpadTaskService;
use App\InternalServices\OpenLaunchChatService\OpenLaunchChatService;
use Brick\PessimisticLocking\PessimisticLocking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Pump\Comment\Service\CommentService;
use Pump\Token\Dao\AgentDAOModel;
use Pump\Token\Dao\TopOfTheMoonDAOModel;
use Pump\Token\DbModel\TokenDbModel;
use Pump\Token\Repository\TokenRepository;
use Pump\User\Repository\UserRepository;
use Pump\User\Services\UserService;
use Web3\Contract;
use Web3\Providers\HttpAsyncProvider;
use Web3\Web3;
use function React\Async\await;

class TokenService
{

    public static $TOKEN_HISTORY_CACHE_FROM_KEY = 'history_cache_on_from_';

    public static $TOKEN_HISTORY_CACHE_TO_KEY = 'history_cache_on_to_';

    public static $TOKEN_HISTORY_CACHE_RT_KEY = 'history_cache_rt_';

    public static $CREATE_TOKEN_KEY_PRE = "create_token_";

    public function tokenDetail($params)
    {
        $innerParams = [
            'tokenIds' => [$params['tokenId']],
        ];
        $innerRt = $this->tokenList($innerParams, true);
        if(!empty($innerRt)){
            return $innerRt[0];
        }else{
            return null;
        }
    }

    public function topOfTheMoon($param)
    {
        $topOfTheMoonTokens = TopOfTheMoonDAOModel::query()->where('status','active')->get()->toArray();
        if(!empty($topOfTheMoonTokens)){
            $tokenIds = array_column($topOfTheMoonTokens,'address');
        }else{
            $tokenIds = config("biz.topOfTheMoonTokens");
        }
        $randomKey = array_rand($tokenIds);
        $innerParams = [
            'tokenIds' => [$tokenIds[$randomKey]],
        ];
        $innerRt = $this->tokenList($innerParams);
        if(!empty($innerRt)){
            return $innerRt[0];
        }else{
            return null;
        }
    }

    private function isTopOfTheMoon($tokenId, $all)
    {
        $tokenId = strtolower($tokenId);
        if(!empty($all)){
            $tokenIds = array_column($all,'address');
        }else{
            $tokenIds = config("biz.topOfTheMoonTokens");

        }
        foreach($tokenIds as $idSingle){
            $idSingle = strtolower($idSingle);
            if($tokenId == $idSingle){
                return true;
            }
        }
        return false;
    }

    public function tokenList($params, $needBeforePrice = false, $needAgentInfo = false)
    {
        /** @var Service $graphService */
        $graphService = resolve(Service::class);

        //{id_in: ["0xbacd7cad68f707715461db07d11c3f2be932accc"], status: "TRADING"}
        $whereContent = [];
        $whereArray = [];
        if(!empty($params['searchKey'])){
            if(substr($params['searchKey'], 0, 2) === '0x'
                || substr($params['searchKey'], 0, 2) === '0X'
                && strlen($params['searchKey']) >= 10
            ){
                $params['tokenIds'] = [$params['searchKey']];
            }else{
                $params['name'] = $params['searchKey'];
            }
        }
        if(!empty($params['symbol'])){
            $whereArray[] = "symbol:\"".$params['symbol']."\"";
        }
        if(!empty($params['tokenIds'])){
            foreach($params['tokenIds'] as &$tokenId){
                $tokenId = strtolower($tokenId);
            }
            $whereArray[] = "id_in:" . json_encode($params['tokenIds']);
        }
        if(!empty($params['statusList'])){
            $whereArray[] = "status_in:" . json_encode($params['statusList']);
        }
        if(!empty($params['name'])){
            $params['name'] = strtolower($params['name']);
            $whereArray[] = "nameLowercase:\"".$params['name']."\"";
        }
        if(!empty($params['creator'])){
            $params['creator'] = strtolower($params['creator']);
            $whereArray[] = "creator:\"".$params['creator']."\"";
        }
        if(!empty($whereArray)){
            $whereStr = "{".implode(",", $whereArray)."}";
        }else{
            $whereStr = '{}';
        }
        $orderBy = $params['orderBy']??'createTimestamp';
        $orderDirection = $params['orderDirection']??'desc';
        $first = $params['pageSize']??100;
        $skip = !empty($params['page']) ? ($params['page']-1)*$first : 0;
        $graphParams = [
            "query" => "query MyQuery {
  tokens(where: $whereStr
         orderBy: $orderBy
         orderDirection: $orderDirection
         first: $first
         skip: $skip
  ) {
    blockNumber
    collateral
    createTimestamp
    creator
    description
    id
    fundingGoal
    imgUrl
    name
    nowPrice
    remainSupply
    status
    currencyAddress
    symbol
    totalSupply
    pairAddress
    currencyAddress
    transactionHash
    updateTimestamp
    sellAt
    airdropRate
  }
}"
        ];
        $rt = $graphService->baseQuery($graphParams);
        $result = [];
        $redis = Redis::connection();
        $topOfTheMoonTokens = TopOfTheMoonDAOModel::query()->where('status','active')->get()->toArray();
        $topOfTheMoonTokensMap = [];
        foreach($topOfTheMoonTokens as $topTmp){
            $topOfTheMoonTokensMap[strtolower($topTmp['address'])] = $topTmp;
        }
        if(!empty($rt['data']) && !empty($rt['data']['tokens'])){
            $creatorIds = array_column($rt['data']['tokens'], 'creator');

            $userInfo = UserRepository::getUsersByAddressList($creatorIds);
            /** @var $userService UserService */
            $userService = resolve('user_service');
            $userInfoFormat = $userService->userDBModelsToUserDTOs($userInfo);
            $userInfoMap = [];
            foreach($userInfoFormat as $user){
                $userInfoMap[$user->address] = $user;
            }

            $tokensIdsRt = array_column($rt['data']['tokens'], 'id');
            $dbModels = TokenRepository::queryTokens(['addressList'=>$tokensIdsRt]);
            $dbModelsMap = [];

            if(!empty($dbModels)){
                foreach($dbModels as $dbModel){
                    $dbModelsMap[$dbModel->address] = $dbModel;
                }
            }
            $currencyCodeList = config("currency");
            /** @var CoingeckoService $coingeckoService */
            $coingeckoService = resolve(CoingeckoService::class);
            foreach($rt['data']['tokens'] as $token){
                if(!empty($dbModelsMap[$token['id']])){
                     $dbModelTmp = $dbModelsMap[$token['id']];
                     $token = array_merge($token, $dbModelTmp->content);
                }
                $totalSupply = $token['totalSupply'];
                //1000000000000000000000000000
                $totalSupply = ceil($totalSupply/(10 ** 18));
                $token['totalSupply'] = $totalSupply;
                $remainSupply = $token['remainSupply'];
                $remainSupply = ceil($remainSupply/(10 ** 18));
                $token['remainSupply'] = $remainSupply;
                $nowPrice = $token['nowPrice'];
                $nowPrice = $nowPrice/(10 ** 18);
                $token['nowPrice'] = $nowPrice;
                if($needBeforePrice && $token['status'] != 'TRADING'){
                    $beforeTrans = $this->getTokenLatestPrice($token['id']);
                    if(!empty($beforeTrans)){
                        $token['beforeTrans'] = $beforeTrans;
                    }
                }
                $totalPrice = $nowPrice * $totalSupply;
                if($token['createTimestamp'] <  Carbon::createFromFormat('Y-m-d H:i:s', '2024-12-28 00:00:00')->timestamp){
                    $token['name'] = '【expired】'.$token['name'];
                    $token['symbol'] =  '【expired】'.$token['symbol'];
                    $token['fundingGoal'] = config('biz.fundingGoalMetis');
                }
                $token['totalPrice'] = number_format($totalPrice,10);
                $token['topOfTheMoon'] = isset($topOfTheMoonTokensMap[$token['id']]);

                if($needAgentInfo){
                   $agentInfo =  AgentDAOModel::query()->where("token_address", $token['id'])->get()->first();
                   if(!empty($agentInfo)){
                       $token['agentInfo'] = $agentInfo->toArray();
                   }
                }

                $replyCnt = $redis->get(CommentService::$TOKEN_COMMNET_COUNT . $token['id']);
                if(empty($replyCnt)){
                    $replyCnt = 0;
                }
                if(!empty($userInfoMap[$token['creator']])){
                    $token['creatorObj'] = $userInfoMap[$token['creator']];
                }
                $token['replyCnt'] = $replyCnt;

                if($token['status'] == 'TRADING'){
                    $token['collateral'] = $token['fundingGoal'];
                }
                if(!empty($token['sellAt'])){
                    $sellAt = Carbon::createFromTimestamp($token['sellAt']);
                    $token['status'] = $sellAt->isFuture() ? 'PRE_SALE' : $token['status'];
                }
                $currencyAddress = $token['currencyAddress'];
                $currencyAddress = strtolower($currencyAddress);
                $currencyCode = $currencyCodeList[$currencyAddress]??'';
                if(!empty($currencyCode)){
                    $token['currencySymbol'] = $currencyCode;
                    $currencyInfo = $coingeckoService->getTokenPrice($currencyCode, 'usd');

                    if($token['status'] != 'TRADING'){
                        if(!empty($currencyInfo) && !empty($currencyInfo[$currencyCode]['usd'])){
                            $currencyPrice = $currencyInfo[$currencyCode]['usd'];
                            $token['totalPriceUsd'] = $currencyPrice * $token['totalPrice'];
                            $token['nowPriceUsd'] = $token['nowPrice'] * $currencyPrice;
                        }
                    }else{
                        if(!empty($currencyInfo) && !empty($currencyInfo[$currencyCode]['usd'])){
                            $pairAddress = strtolower($token['pairAddress']);
                            $relativePrice = $this->getPriceByNetSwap($pairAddress, $token['currencyAddress']);
                            $currencyPrice = $currencyInfo[$currencyCode]['usd'];
                            $token['nowPrice'] = $relativePrice;
                            $token['nowPriceUsd'] = $relativePrice * $currencyPrice;
                            $token['totalPrice'] = $token['nowPrice'] * $totalSupply;
                            $token['totalPriceUsd'] = $currencyPrice * $token['totalPrice'];
                        }
                    }

                }
                $result[] = $token;
            }
        }
        return $result;
    }

    public function userBoughtTokens($params)
    {
        /** @var Service $graphService */
        $graphService = resolve(Service::class);

        //{id_in: ["0xbacd7cad68f707715461db07d11c3f2be932accc"], status: "TRADING"}
        $whereContent = [];
        $whereArray = [];
        if(empty($params['tokenAmountGt'])){
            $params['tokenAmountGt'] = 1;
        }
        if(!empty($params['user'])){
            $params['user'] = strtolower($params['user']);
            $whereArray[] = "user:\"".$params['user']."\"";
        }
        if(!empty($params['tokenAmountGt'])){
            $whereArray[] = "tokenAmount_gt:\"".$params['tokenAmountGt']."\"";
        }
        if(!empty($whereArray)){
            $whereStr = "{".implode(",", $whereArray)."}";
        }else{
            $whereStr = '{}';
        }
        $orderBy = $params['orderBy']??'updateTimestamp';
        $orderDirection = $params['orderDirection']??'desc';
        $first = $params['first']??1000;

        $graphParams = [
            "query" => "query MyQuery {
  userMeMeTokenBalances(where: $whereStr
     orderBy: $orderBy
     orderDirection: $orderDirection
     first: $first
  ) {
    createTimestamp
    id
    token
    tokenAmount
    tokenName
    updateTimestamp
    user
  }
}"
        ];
        $rt = $graphService->baseQuery($graphParams);
        $result = [];
        if(!empty($rt['data']) && !empty($rt['data']['userMeMeTokenBalances'])){
            $userIds = array_column($rt['data']['userMeMeTokenBalances'], 'user');
            $userIds = array_unique($userIds);
            $userList = UserRepository::getUsersByAddressList($userIds);
            $userMap = [];
            foreach($userList as $user){
                $userMap[$user->address] = $user;
            }
            $tokenIds = array_column($rt['data']['userMeMeTokenBalances'], 'token');
            $tokenIds = array_unique($tokenIds);
            $tokenList = $this->tokenList(
                [
                    'tokenIds'=>$tokenIds,
                ]
            );
            $tokenMap = [];
            if(!empty($tokenList)){
                foreach($tokenList as $token){
                    $tokenMap[$token['id']] = $token;
                }
            }
            if(!empty($rt['data']['userMeMeTokenBalances'])){
                foreach($rt['data']['userMeMeTokenBalances'] as $balance){
                    if(!empty($tokenMap[$balance['token']])){
                        $single = $tokenMap[$balance['token']];
                        $single['tokenAmount'] = $balance['tokenAmount'];
                        $single['createTimestamp'] = $balance['createTimestamp'];
                        $single['updateTimestamp'] = $balance['updateTimestamp'];
                        $result[] = $single;
                    }
                }
            }
        }
        return $result;
    }

    public static $RELATIVE_PRICE_KEY = "RELATIVE_PRICE_";
    private function getPriceByNetSwap($pairAddress, $currencyAddress)
    {   $redis = Redis::connection();
        if(!empty($redis->get(self::$RELATIVE_PRICE_KEY . $pairAddress . '_' . $currencyAddress))){
            return $redis->get(self::$RELATIVE_PRICE_KEY . $pairAddress . '_' . $currencyAddress);
        }
        $abiObj = config("abi.NetswapPair");

        $web3 = new Web3(new HttpAsyncProvider(env('METIS_RPC_URL','https://sepolia.metisdevops.link')),30);

        $contract = new Contract($web3->provider, $abiObj);
        $tokenPrice = 0;
        $functionResult = [];
        $rt = $contract->at($pairAddress)->call("getReserves",[], function($err, $result) use(&$functionResult) {
            $functionResult = $result;
        });
        $token0Result = null;
        $token1Result = null;
        $rt2 = $contract->at($pairAddress)->call("token0",[], function($err, $result) use(&$token0Result) {
            $token0Result = $result;
        });
        $rt3 = $contract->at($pairAddress)->call("token1",[], function($err, $result) use(&$token1Result) {
            $token1Result = $result;
        });
        await($rt);
        await($rt2);
        await($rt3);
        $currencyToken = null;
        $memeToken = null;
        $currencyAmount = 0;
        $memeTokenAmount = 0;
        if(!empty($token1Result[0]) && !empty($token0Result[0]) && !empty($functionResult['_reserve0']) && !empty($functionResult['_reserve1'])){
            if(strtolower($token0Result[0]) == strtolower($currencyAddress)){
                $currencyToken = $token0Result[0];
                $currencyAmount = $functionResult['_reserve0']->toString();
                $memeToken =   $token1Result[0];
                $memeTokenAmount = $functionResult['_reserve1']->toString();
            }else{
                $currencyToken = $token1Result[0];
                $currencyAmount = $functionResult['_reserve1']->toString();
                $memeToken = $token0Result[0];
                $memeTokenAmount = $functionResult['_reserve0']->toString();
            }
            $relativePrice = number_format($currencyAmount/$memeTokenAmount,20);
            $redis->command('set',[self::$RELATIVE_PRICE_KEY . $pairAddress . '_' . $currencyAddress , $relativePrice, 'EX', 300]);
            return $relativePrice;
        }
        return 0;
    }

    public function tokenHolders($params)
    {
        /** @var Service $graphService */
        $graphService = resolve(Service::class);

        //{id_in: ["0xbacd7cad68f707715461db07d11c3f2be932accc"], status: "TRADING"}
        $whereContent = [];
        $whereArray = [];
        if(empty($params['tokenAmountGt'])){
            $params['tokenAmountGt'] =   number_format(10000 * (10 ** 18),0,'.','');
        }
        if(!empty($params['token'])){
            $params['token'] = strtolower($params['token']);
            $whereArray[] = "token:\"".$params['token']."\"";
        }
        if(!empty($params['tokenAmountGt'])){
            $whereArray[] = "tokenAmount_gt:\"".$params['tokenAmountGt']."\"";
        }
        if(!empty($whereArray)){
            $whereStr = "{".implode(",", $whereArray)."}";
        }else{
            $whereStr = '{}';
        }
        $orderBy = $params['orderBy']??'tokenAmount';
        $orderDirection = $params['orderDirection']??'desc';
        $first = $params['first']??10;

        $graphParams = [
            "query" => "query MyQuery {
  userMeMeTokenBalances(where: $whereStr
     orderBy: $orderBy
     orderDirection: $orderDirection
     first: $first
  ) {
    createTimestamp
    id
    token
    tokenAmount
    tokenName
    updateTimestamp
    user
  }
}"
        ];
        $rt = $graphService->baseQuery($graphParams);
        $result = [];
        $bondingCurveAddress = config('biz.bondingCurveAddress');

        if(!empty($rt['data']) && !empty($rt['data']['userMeMeTokenBalances'])){
            $userIds = array_column($rt['data']['userMeMeTokenBalances'], 'user');
            $userIds = array_unique($userIds);
            $userList = UserRepository::getUsersByAddressList($userIds);
            $userMap = [];
            foreach($userList as $user){
                $userMap[$user->address] = $user;
            }
            foreach($rt['data']['userMeMeTokenBalances'] as $balance){
                $single = [];
                $single['tokenAmount'] = $balance['tokenAmount'];
                $single['userAddress'] = $balance['user'];
                if(!empty($userMap[$balance['user']])){
                    $single['userName'] = $userMap[$balance['user']]->nickName;
                    $single['userImg'] = $userMap[$balance['user']]->headImgUrl;
                    $single['type'] = 'user';
                }else{
                    if($this->isBondingCurve($balance['user'], $bondingCurveAddress)){
                        $single['userName'] = 'bondingCurve';
                        $single['type'] = 'bondingCurve';
                    }else if(strtolower($balance['user']) == strtolower(env('LP_MANAGER_ADDRESS', '0xb673B8a4c24B450c391E7756E2FbF62DF436B630'))){
                        $single['userName'] = 'lpManager';
                        $single['type'] = 'lpManager';
                    }else{
                        $single['userName'] = 'other';
                        $single['type'] = 'other';
                    }

                }
                //799999992827630470754520845
                $amount = $balance['tokenAmount']/1000000000000000000;
                $total = 1000000000;
                $rate = $amount / $total;
                $single['rate'] = round($rate,4);
                $result[] = $single;
            }
        }
        return $result;
    }

    private function isBondingCurve($address, $bondingCurveAddresses)
    {
        foreach($bondingCurveAddresses as $bondingCurveAddressSingle){
            if(strtolower($bondingCurveAddressSingle) == strtolower($address)){
                return true;
            }
        }
        return false;
    }

    public function tradingList($params)
    {
        /** @var Service $graphService */
        $graphService = resolve(Service::class);
        $whereArray = [];
        if(!empty($params['token'])){
            $params['token'] = strtolower($params['token']);
            $whereArray[] = "token:\"".$params['token']."\"";
        }
        if(!empty($params['user'])){
            $params['user'] = strtolower($params['user']);
            $whereArray[] = "user:\"".$params['user']."\"";
        }
        if(!empty($params['type'])){
            $whereArray[] = "type:\"".$params['type']."\"";
        }
        if(!empty($whereArray)){
            $whereStr = "{".implode(",", $whereArray)."}";
        }else{
            $whereStr = '{}';
        }
        $currentPage = $params['page']??1;
        $pageSize = $params['perPage']??10;
        $orderBy = $params['orderBy']??'createTimestamp';
        $orderDirection = $params['orderDirection']??'desc';
        $first = $pageSize;
        $skip = ($currentPage - 1) * $pageSize;
        $graphParams = [
            "query" => "query MyQuery {
  transactions(where: $whereStr
     orderBy: $orderBy
     orderDirection: $orderDirection
     first: $first
     skip: $skip
  ) {
    blockNumber
    createTimestamp
    id
    metisAmount
    token
    tokenAmount
    tokenName
    transactionHash
    type
    user
  }
}"
        ];

        $rt = $graphService->baseQuery($graphParams);
        $items = [];
        $result = [];
        $pagination = [
            "currentPage" => $currentPage,
        ];
        if(!empty($rt['data']) && !empty($rt['data']['transactions'])){
            $userIds = array_column($rt['data']['transactions'], 'user');
            $userIds = array_unique($userIds);
            $userList = UserRepository::getUsersByAddressList($userIds);
            $userMap = [];
            $pagination['total'] = count($rt['data']['transactions']);
            foreach($userList as $user){
                $userMap[$user->address] = $user;
            }
            foreach($rt['data']['transactions'] as $transaction){
                $single = [];
                $single['token'] = $transaction['token'];
                $single['type'] = $transaction['type'];
                $single['tokenAmount'] = number_format($transaction['tokenAmount']/(10 ** 18),1);
                $single['transactionHash'] = $transaction['transactionHash'];
                $single['currencyAmount'] =  number_format($transaction['metisAmount']/(10 ** 18),4);
                $single['userAddress'] = $transaction['user'];
                $single['createTimestamp'] = $transaction['createTimestamp'];
                if(!empty($userMap[$transaction['user']])){
                    $single['userName'] = $userMap[$transaction['user']]->nickName;
                    $single['userImg'] = $userMap[$transaction['user']]->headImgUrl;
                }else{
                    $single['userName'] = $transaction['user'];
                }

                $items[] = $single;
            }
        }
        $result['pagination'] = $pagination;
        $result['items'] = $items;
        return $result;
    }

    public function getTokenTradingAmount($params)
    {
        /** @var Service $graphService */
        $graphService = resolve(Service::class);
        $whereArray = [];
        if(!empty($params['token'])){
            $params['token'] = strtolower($params['token']);
            $whereArray[] = "token:\"".$params['token']."\"";
        }
        if(!empty($params['startTime'])){
            $whereArray[] = "createTimestamp_gte:\"".$params['startTime']."\"";
        }
        if(!empty($params['endTime'])){
            $whereArray[] = "createTimestamp_lte:\"".$params['endTime']."\"";
        }

        if(!empty($whereArray)){
            $whereStr = "{".implode(",", $whereArray)."}";
        }else{
            $whereStr = '{}';
        }
        $graphParams = [
            "query" => "query MyQuery {
  transactions(where: $whereStr
  ) {
    tokenAmount
  }
}"
        ];
        $rt = $graphService->baseQuery($graphParams);
        $result = 0;
        if(!empty($rt['data']) && !empty($rt['data']['transactions'])){
            foreach($rt['data']['transactions'] as $transaction){
                $result += $transaction['tokenAmount'];
            }
        }
        return sprintf("%.0f", $result);
    }

    public function getTokenLatestPrice($token)
    {
        $to = Carbon::now()->subHour()->timestamp;
        /** @var Service $graphService */
        $graphService = resolve(Service::class);
        $whereArray = [];
        $whereArray[] = "token:\"".$token."\"";
        $whereArray[] = "createTimestamp_lte:\"".$to."\"";
        $whereStr = "{".implode(",", $whereArray)."}";
        $orderBy = $params['orderBy']??'createTimestamp';
        $orderDirection = 'desc';
        $first = 1;
        $graphParams = [
            "query" => "query MyQuery {
  transactions(where: $whereStr
     orderBy: $orderBy
     orderDirection: $orderDirection
     first: $first
  ) {
    blockNumber
    createTimestamp
    id
    metisAmount
    token
    tokenAmount
    tokenName
    tokenPrice
    transactionHash
    type
    user
  }
}"
        ];
        $rt = $graphService->baseQuery($graphParams);
        if(!empty($rt['data']) && !empty($rt['data']['transactions'])){
            $trans = $rt['data']['transactions'][0];
            $trans['tokenPrice'] = $trans['tokenPrice']/(10 ** 18);
            return $trans;
        }
        return null;
    }

    public function getTokenHistory($param)
    {
        $t = [];
        $o = [];
        $h = [];
        $l = [];
        $c = [];
        $v = [];

        $redis = Redis::connection();

        $token = strtolower($param['symbol']);
        $resolution = $param['resolution'];
        $from = $param['from'];
        $to = $param['to'];

        $canUseCache = false;
        $needFill = false;
        $fromCache = $redis->get(self::$TOKEN_HISTORY_CACHE_FROM_KEY .$token.'_'.$resolution);
        $toCache = $redis->get(self::$TOKEN_HISTORY_CACHE_TO_KEY .$token.'_'.$resolution);
        if(!empty($fromCache) && !empty($toCache)){
            if($fromCache <= $from){
                $canUseCache = true;
                if($toCache < $to){
                    $needFill = true;
                }
            }
        }
        $cacheRt = [];
        $fromFill = $from;
        if($canUseCache){
            if($needFill){
                //part from cache,part from graph
                $fromFill = $toCache;
                $cacheRt = json_decode($redis->get(self::$TOKEN_HISTORY_CACHE_RT_KEY.$token.'_'.$resolution.'_'.$fromCache.'_'.$toCache), true);
                Log::info("need fill history cache:".json_encode($cacheRt));
                if(!empty($cacheRt) && !empty($cacheRt['t'])){
                    $i = 0;
                    $count = 0;
                    foreach($cacheRt['t'] as $tSingle){
                        $count++;
                        if($tSingle < $from){
                            $i++;
                        }
                    }
                    if($i > 0){
                        array_splice($cacheRt['t'], 0, $i);

                        array_splice($cacheRt['o'], 0, $i);

                        array_splice($cacheRt['h'], 0, $i);

                        array_splice($cacheRt['l'], 0, $i);

                        array_splice($cacheRt['c'], 0, $i);

                        array_splice($cacheRt['v'], 0, $i);
                    }
                }
            }else{
                //all from cache
                $cacheRt = json_decode($redis->get(self::$TOKEN_HISTORY_CACHE_RT_KEY.$token.'_'.$resolution.'_'.$fromCache.'_'.$toCache), true);
                Log::info("all from cache history cache:".json_encode($cacheRt));

                if(!empty($cacheRt) && !empty($cacheRt['t'])){
                    $i = 0;
                    $j = 0;
                    $count = 0;
                    foreach($cacheRt['t'] as $tSingle){
                        $count++;
                        if($tSingle < $from){
                            $i++;
                        }
                        if($tSingle > $to){
                            $j++;
                        }
                    }
                    if($i > 0){
                        array_splice($cacheRt['t'], $count-1-$j);
                        array_splice($cacheRt['t'], 0, $i);

                        array_splice($cacheRt['o'], $count-1-$j);
                        array_splice($cacheRt['o'], 0, $i);

                        array_splice($cacheRt['h'], $count-1-$j);
                        array_splice($cacheRt['h'], 0, $i);

                        array_splice($cacheRt['l'], $count-1-$j);
                        array_splice($cacheRt['l'], 0, $i);

                        array_splice($cacheRt['c'], $count-1-$j);
                        array_splice($cacheRt['c'], 0, $i);

                        array_splice($cacheRt['v'], $count-1-$j);
                        array_splice($cacheRt['v'], 0, $i);

                    }
                    return $cacheRt;
                }
            }
        }

        /** @var Service $graphService */
        $graphService = resolve(Service::class);
        $whereArray = [];
        $whereArray[] = "token:\"".$token."\"";
        $whereArray[] = "createTimestamp_gt:\"".$fromFill."\"";
        $whereArray[] = "createTimestamp_lte:\"".$to."\"";
        $whereStr = "{".implode(",", $whereArray)."}";
        $currentPage = 1;
        $pageSize = 1000;
        $orderBy = $params['orderBy']??'createTimestamp';
        $orderDirection = 'asc';
        $first = $pageSize;
        $rt = [];
        Log::info("can use cache?:$canUseCache,$needFill");
        do{
            $skip = ($currentPage - 1) * $pageSize;
            $graphParams = [
                "query" => "query MyQuery {
  transactions(where: $whereStr
     orderBy: $orderBy
     orderDirection: $orderDirection
     first: $first
     skip: $skip
  ) {
    blockNumber
    createTimestamp
    id
    metisAmount
    token
    tokenAmount
    tokenName
    tokenPrice
    transactionHash
    type
    user
  }
}"
            ];
            $rtTmp = $graphService->baseQuery($graphParams);

            if(!empty($rtTmp['data']) && !empty($rtTmp['data']['transactions'])){
                $rt = array_merge($rt, $rtTmp['data']['transactions']);
            }
            $currentPage++;
        }while(!empty($rtTmp['data']) && !empty($rtTmp['data']['transactions']));
        if(empty($rt)){
            if($canUseCache && !empty($cacheRt)){
                return $cacheRt;
            }else{
                return [
                    "s" => "ok",
                    "t" => $t, // 时间戳
                    "o" => $o, // 开盘价
                    "h" => $h, // 最高价
                    "l" => $l, // 最低价
                    "c" => $c, // 收盘价
                    "v" => $v  // 成交量
                ];
            }
        }
        $dateList = $this->getDateList($resolution, $fromFill, $to);
        $dateListItem = $dateList['dateList'];

        $contentList = [];
        for($i = 1; $i < count($dateListItem); $i++){
            $contentList[$dateListItem[$i]] = [];
        }

        if(!empty($dateListItem) && count($dateListItem) > 1){
            if(!empty($rt)){
                foreach($rt as &$transaction){
                    $this->inWhichPeriod($dateListItem, $transaction, $contentList);
                }
            }

            foreach ($contentList as $time => $transactions){
                $oPrice = 0;
                $hPrice = 0;
                $lPrice = 0;
                $cPrice = 0;
                $amount = 0;
                if(!empty($transactions)){

                    $oPrice = $lPrice = $transactions[0]['tokenPrice']/(10**18);
                    $cPrice = $transactions[count($transactions) - 1]['tokenPrice']/(10**18);
                    foreach($transactions as $transaction){
                        $price = $transaction['tokenPrice']/(10**18);
                        if($price > $hPrice){
                            $hPrice = $price;
                        }
                        if($price < $lPrice){
                            $lPrice = $price;
                        }
                        $amount += $transaction['tokenAmount']/1000000000000000000;
                    }
                    $t[] = $time;
                    $o[] = $oPrice;
                    $h[] = $hPrice;
                    $l[] = $lPrice;
                    $c[] = $cPrice;
                    $v[] = $amount;
                }
            }
        }
        $result = [];
        if(!empty($cacheRt)){
            Log::info("before merge,cache:".json_encode($cacheRt));
            $dataCurrent = [
                "t" => $t, // 时间戳
                "o" => $o, // 开盘价
                "h" => $h, // 最高价
                "l" => $l, // 最低价
                "c" => $c, // 收盘价
                "v" => $v  // 成交量
            ];
            Log::info("before merge,data:".json_encode($dataCurrent));
            $result = [
                "t" => !empty($cacheRt['t'])?array_merge($cacheRt['t'],$t):$t, // 时间戳
                "o" => !empty($cacheRt['o'])?array_merge($cacheRt['o'],$o):$o, // 开盘价
                "h" => !empty($cacheRt['h'])?array_merge($cacheRt['h'],$h):$h, // 最高价
                "l" => !empty($cacheRt['l'])?array_merge($cacheRt['l'],$l):$l, // 最低价
                "c" => !empty($cacheRt['c'])?array_merge($cacheRt['c'],$c):$c, // 收盘价
                "v" => !empty($cacheRt['v'])?array_merge($cacheRt['v'],$v):$v,  // 成交量
            ];
        }else{
            $result = [
                "t" => $t, // 时间戳
                "o" => $o, // 开盘价
                "h" => $h, // 最高价
                "l" => $l, // 最低价
                "c" => $c, // 收盘价
                "v" => $v  // 成交量
            ];
        }
        $this->cacheHistory($token, $result, $resolution, $from, $to);

        return $result;
    }

    private function cacheHistory($symbol, $result, $resolution,$from,$to)
    {
        $redis = Redis::connection();
        $redis->command('set',[self::$TOKEN_HISTORY_CACHE_FROM_KEY.$symbol.'_'.$resolution, $from, 'EX',  300]);
        $redis->command('set',[self::$TOKEN_HISTORY_CACHE_TO_KEY.$symbol.'_'.$resolution, $to, 'EX',  300]);
        $redis->command('set',[self::$TOKEN_HISTORY_CACHE_RT_KEY.$symbol.'_'.$resolution.'_'.$from.'_'.$to, json_encode($result),'EX',  300]);

    }

    private function inWhichPeriod($dates, $transaction, &$contentList)
    {
        for($i = 0; $i < count($dates)-1; $i++){
            $start = $dates[$i];
            $end = $dates[$i + 1];
            if($transaction['createTimestamp'] > $start && $transaction['createTimestamp'] <= $end){

                $contentList[$end][] = $transaction;
            }
        }
    }

    public function searchSymbols($params)
    {
        $tokenRt = $this->tokenList(
            [
                'searchKey' => $params['query'],
            ],
            false
        );
        $result = [];
        if(!empty($tokenRt)){
            foreach($tokenRt as $token){
                $single = [
                    'symbol' => $token['symbol'],
                    'ticker' => $token['name'],
                    'description' => $token['description'],
                    'exchange' => 'TokenExchange',
                    'type' => 'crypto',
                ];
                $result[] = $single;
            }
        }
        return $result;
    }

    public function resolveSymbol($params)
    {
//        {
//            "name": "AAPL",
//	"exchange-traded": "NasdaqNM",
//	"exchange-listed": "NasdaqNM",
//	"timezone": "America/New_York",
//	"minmov": 1,
//	"minmov2": 0,
//	"pointvalue": 1,
//	"session": "0930-1630",
//	"has_intraday": false,
//	"visible_plots_set": "ohlcv",
//	"description": "Apple Inc.",
//	"type": "stock",
//	"supported_resolutions": ["D", "2D", "3D", "W", "3W", "M", "6M"],
//	"pricescale": 100,
//	"ticker": "AAPL",
//	"logo_urls": ["https://s3-symbol-logo.tradingview.com/apple.svg"],
//	"exchange_logo": "https://s3-symbol-logo.tradingview.com/country/US.svg"
//}

        $tokenRt = $this->tokenList(
            [
                'searchKey' => $params['symbol'],
            ],
            false
        );

        $result = null;
        if(!empty($tokenRt)){
            $token = $tokenRt[0];
            $result = [
                'name' => $token['name'],
                'ticker' => $token['id'],
                'pointvalue'=>1,
                'description' => $token['symbol'],
                'type' => 'crypto',
                'session' => '24x7',
                'exchange' => 'TokenExchange',
                'listed_exchange' => '',
                'timezone' => 'UTC',
                'minmov' => 1,
                'pricescale' => 100,
                'has_intraday' => true,
                'has_daily' => true,
                'has_weekly_and_monthly' => true,
                'currency_code' => 'METIS',
                "supported_resolutions"=>["1", "5", "1D", "1W"]
            ];
        }
        return $result;
    }



    private function getDateList($res, $from, $to)
    {
        //"1", "5", "30", "60", "1D", "1W", "1M"
        $dateList = [];
        $toTmp = $to;
        switch ($res){
            case "1":
                while($toTmp > $from){
                    $toTmpObj = Carbon::createFromTimestamp($toTmp);
                    array_unshift($dateList, $toTmpObj->endOfMinute()->timestamp);
                    $toTmp = $toTmp - 60;
                }
                array_unshift($dateList, $toTmp);
                break;
            case "5":
                while($toTmp > $from){
                    $toTmpObj = Carbon::createFromTimestamp($toTmp);
                    array_unshift($dateList, $toTmpObj->endOfMinute()->timestamp);
                    $toTmp = $toTmp - (5 * 60);
                }
                array_unshift($dateList, $toTmp);
                break;
            case "10":
                while($toTmp > $from){
                    $toTmpObj = Carbon::createFromTimestamp($toTmp);
                    array_unshift($dateList, $toTmpObj->endOfMinute()->timestamp);
                    $toTmp = $toTmp - (10 * 60);
                }
                array_unshift($dateList, $toTmp);
                break;
            case "1H":
                while($toTmp > $from){
                    $toTmpObj = Carbon::createFromTimestamp($toTmp);
                    array_unshift($dateList, $toTmpObj->endOfHour()->timestamp);
                    $toTmp = $toTmp - (60 * 60);
                }
                array_unshift($dateList, $toTmp);
                break;
            case "12H":
                while($toTmp > $from){
                    $toTmpObj = Carbon::createFromTimestamp($toTmp);
                    array_unshift($dateList, $toTmpObj->endOfHour()->timestamp);
                    $toTmp = $toTmp - (60 * 60 * 12);
                }
                array_unshift($dateList, $toTmp);
                break;

            case "1D":
                while($toTmp > $from){
                    $toTmpObj = Carbon::createFromTimestamp($toTmp);
                    array_unshift($dateList, $toTmpObj->endOfDay()->timestamp);
                    $toTmp =  $toTmpObj->subDay()->endOfDay()->timestamp;
                }
                array_unshift($dateList, $toTmp);
                break;
            case "1W":
                while($toTmp > $from){
                    $toTmpObj = Carbon::createFromTimestamp($toTmp);
                    array_unshift($dateList, $toTmpObj->endOfDay()->timestamp);
                    $toTmp =  $toTmpObj->subDays(7)->endOfDay()->timestamp;
                }
                array_unshift($dateList, $toTmp);
                break;
            default:
                throw new DomainException("unsupported date range");
        }
        return [
            "dateList" => $dateList,
            "res" => $res
        ];
    }

    public function createToken($params): TokenDbModel
    {
        /** @var PessimisticLocking $lock */
        $lock = resolve('locker');
        return $lock->process(self::$CREATE_TOKEN_KEY_PRE . $params['address'], 10, 100000, 3, function () use ($params) {
            return DB::transaction(function () use ($params) {
                $queryParams = [
                    'addressList'=> [$params['address']]
                ];
                $existsToken = TokenRepository::queryTokens($queryParams);
                if(empty($existsToken)){
                    $tokenDbModel = $this->createParamsToDbModel($params);

                    if(!empty($params['coBuildAgent'])){
                        $params['coBuildAgent']['tokenAddress'] = $params['address'];
                        $params['coBuildAgent']['agentType'] = $params['agentType'];
                        if(!empty($params['coBuildAgent']["knowledgeUrl"])){
                            $params['coBuildAgent']["knowledgeStr"] = $markdown = file_get_contents($params['coBuildAgent']["knowledgeUrl"]);
                        }
                        $agentModel = $this->createParamsToCoAgentDbModel($params['coBuildAgent']);
                        /** @var \App\InternalServices\CoBuildAgent\CoBuildAgentInternalService $coBuildAgentInternalService */
                        $coBuildAgentInternalService = resolve('co_build_agent_service');
                        $tokenAddress = $params['address'];
                        $description = $params['coBuildAgent']["description"];
                        $tagLine = $params['coBuildAgent']["tagLine"];
                        $greeting = $params['coBuildAgent']["greeting"];
                        $airdropRate = $params['airdropRate'];
                        $knowledgeUrl = $params['coBuildAgent']["knowledgeUrl"];
                        $knowledgeStr = $params['coBuildAgent']["knowledgeStr"];
                        $graphParams = [
                            "query" => "query MyQuery {
# Write your query or mutation here
mutation CreateAgent {
  createAgent(
    agent: {
      uid:  $tokenAddress
      name: $agentModel->agent_name
      tagline: $tagLine
      description: $description
      greeting: $greeting
      airdropAllocation: $airdropRate
    }
    knowledge: {
      text: $knowledgeStr
      url: $knowledgeUrl
    }
  ) {
    id
    uid
    name
    tagline
    description
    greeting
    airdropAllocation
    ownerUserId
  }
}",
                            "operationName" => "CreateAgent"
                        ];
                        $graphHeaders = [
                            "Authorization"=>"Bearer xVXyLpIV2MS6C6UzpJlf",
                            "x-user-id" => $params['creatorObj']->id
                        ];
                        $createRt = $coBuildAgentInternalService->agentPost("", $graphParams, $graphHeaders, false);
                        $createRt = $createRt['data']['createAgent'];
                        if(!empty($params['airdropRate'])){
                            /** @var AirdropService $airdropService */
                            $airdropService = resolve('airdrop_service');

                            $airdropActivity = $airdropService->airdropPost('insert-airdrop-activity', [
                                "tokenAddress"=> $params['address'],
                                "tokenName" => $tokenDbModel->name,
                                "tokenSymbol" => $tokenDbModel->symbol,
                                "tokenDecimals" => "1000000000000000000",
                                "name" => "airdrop_".$params['address'],
                                "startTime" => Carbon::now()->timestamp,
                                "endTime" => Carbon::now()->addYears(2)->timestamp,
                            ],
                                [
                                    "apiToken"=>config("internal.airdrop_service_api_key")
                                ]
                            );
                            $tokenDbModel->content['airdropActivityId'] = $airdropActivity['id'];
                        }
                        $agentContentObj = json_decode($agentModel->content, true);
                        $agentContentObj['outAgentId'] = $createRt['id'];
                        $agentModel->content = json_encode($agentContentObj);
                        $agentModel->out_agent_id = $createRt['id'];
                        $agentModel->save();

                        $tokenDbModel->coBuildAgentId = $agentModel->id;
                    }
                    $rt = TokenRepository::createToken($tokenDbModel);
                    return $rt;
                }else{
                    return $existsToken[0];
                }
            });
        });
    }

    public function syncTokenTransaction($user, $currencyAmount, $currencyType, $transactionHash, $transactionType)
    {
        /** @var AirdropService $airdropService */
        $airdropService = resolve('airdrop_service');

        /** @var $taskPointService LazpadTaskService */
        $taskPointService = resolve('lazpad_task_service');
        $invitedByWho = $taskPointService->getDataWithHeaders("user/invitedByWho",
            [
                "headers"=>[
                    "x-server-call" => "true",
                    "traceId"=> app('requestId')
                ],
                "query"=>[
                    "userId"=>$user->id
                ]
            ]);
        if(empty($invitedByWho)){
            return;
        }
        try{
            $airdropService->airdropPost("insert-service-fee", [
                "userAddress"=>$invitedByWho['ethAddress'],
                "bizId" => $transactionHash,
                "bizType" => $transactionType,
                "amount" => floor($currencyAmount/1000),
            ],
                ["apiToken"=>config("internal.airdrop_service_api_key")], false);

        }catch (\Throwable $e){
            Log::error("insert service fee error:$e");
        }
    }

    public function createParamsToCoAgentDbModel($params)
    {
        $model = new AgentDAOModel();
        $model->agent_name = $params['agentName'];
        $model->token_address = $params['tokenAddress'];
        $model->status = 'running';
        $content = [];
        if(!empty($params['tagLine'])){
            $content['tagLine'] = $params['tagLine'];
        }
        if(!empty($params['description'])){
            $content['description'] = $params['description'];
        }
        if(!empty($params['greeting'])){
            $content['greeting'] = $params['greeting'];
        }
        if(!empty($params['knowledgeUrl'])){
            $content['knowledgeUrl'] = $params['knowledgeUrl'];
        }
        if(!empty($params['knowledgeStr'])){
            $content['knowledgeStr'] = $params['knowledgeStr'];
        }
        $content['dayTotalLimit'] = 100;
        $model->content = json_encode($content);
        $model->type = $params['agentType'];
        $model->save();
        return $model;
    }

    public function createParamsToDbModel($params): TokenDbModel
    {
        $tokenDbModel = new TokenDbModel();
        $tokenDbModel->name = $params['name'];
        $tokenDbModel->address = $params['address'];
        $tokenDbModel->desc =$params['desc'];
        $content = [];
        if(!empty($params['website'])){
            $content['website'] = $params['website'];
        }
        if(!empty($params['twitterLink'])){
            $content['twitterLink'] = $params['twitterLink'];
        }
        if(!empty($params['telegramLink'])){
            $content['telegramLink'] = $params['telegramLink'];
        }
        $tokenDbModel->content = $content;
        $tokenDbModel->imgUrl = $params['imgUrl'];
        $tokenDbModel->symbol = $params['symbol'];
        $tokenDbModel->creator = $params['creator'];
        if(!empty($params['sellAt'])){
            $params['sellAt'] = date('Y-m-d H:i:s', $params['sellAt']);
        }
        $tokenDbModel->sellAt = $params['sellAt'];
        $tokenDbModel->aiAgentType = $params['agentType'];
        $tokenDbModel->airdropRate = $params['airdropRate'];
        return $tokenDbModel;
    }

}
