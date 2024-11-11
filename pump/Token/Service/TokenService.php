<?php

namespace Pump\Token\Service;

use App\InternalServices\Coingecko\CoingeckoService;
use App\InternalServices\GraphService\Service;
use Illuminate\Support\Facades\DB;
use Pump\Token\DbModel\TokenDbModel;
use Pump\Token\Repository\TokenRepository;
use Pump\User\Repository\UserRepository;
use Web3\Contract;
use Web3\Providers\HttpAsyncProvider;
use Web3\Web3;
use function React\Async\await;

class TokenService
{

    public function tokenDetail($params)
    {
        $innerParams = [
            'tokenIds' => [$params['tokenId']],
        ];
        $innerRt = $this->tokenList($innerParams);
        if(!empty($innerRt)){
            return $innerRt[0];
        }else{
            return null;
        }
    }

    public function tokenList($params)
    {
        /** @var Service $graphService */
        $graphService = resolve(Service::class);

        //{id_in: ["0xbacd7cad68f707715461db07d11c3f2be932accc"], status: "TRADING"}
        $whereContent = [];
        $whereArray = [];
        if(!empty($params['tokenIds'])){
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
        $first = $params['first']??100;
        $graphParams = [
            "query" => "query MyQuery {
  tokens(where: $whereStr
         orderBy: $orderBy
         orderDirection: $orderDirection
         first: $first
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
  }
}"
        ];
        $rt = $graphService->baseQuery($graphParams);
        $result = [];
        if(!empty($rt['data']) && !empty($rt['data']['tokens'])){
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
                $totalPrice = $nowPrice * $totalSupply;
                $token['totalPrice'] = ceil($totalPrice);
                $currencyAddress = $token['currencyAddress'];
                $currencyAddress = strtolower($currencyAddress);
                $currencyCode = $currencyCodeList[$currencyAddress]??'';
                if(!empty($currencyCode)){
                    $currencyInfo = $coingeckoService->getTokenPrice($currencyCode, 'usd');

                    if($token['status'] != 'TRADING'){
                        if(!empty($currencyInfo) && !empty($currencyInfo[$currencyCode]['usd'])){
                            $currencyPrice = $currencyInfo[$currencyCode]['usd'];
                            $token['totalPriceUsd'] = $currencyPrice * $token['totalPrice'];
                        }
                    }else{
                        if(!empty($currencyInfo) && !empty($currencyInfo[$currencyCode]['usd'])){
                            $pairAddress = strtolower($token['pairAddress']);
                            $relativePrice = $this->getPriceByNetSwap($pairAddress, $token['currencyAddress']);
                            $token['totalPriceUsd'] = $currencyPrice * $relativePrice;
                            $token['totalPrice'] = $relativePrice;
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

    private function getPriceByNetSwap($pairAddress, $currencyAddress)
    {
        $abiObj = config("abi.NetswapPair");

        $web3 = new Web3(new HttpAsyncProvider('https://sepolia.metisdevops.link'),30);

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
            $relativePrice = number_format($currencyAmount/$memeTokenAmount,5);
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
            $params['tokenAmountGt'] = 1000;
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
                }else{
                    $single['userName'] = 'bondingCurve';
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

    public function tradingList($params)
    {
        /** @var Service $graphService */
        $graphService = resolve(Service::class);
        $whereArray = [];
        if(!empty($params['token'])){
            $params['token'] = strtolower($params['token']);
            $whereArray[] = "token:\"".$params['token']."\"";
        }
        if(!empty($whereArray)){
            $whereStr = "{".implode(",", $whereArray)."}";
        }else{
            $whereStr = '{}';
        }
        $orderBy = $params['orderBy']??'createTimestamp';
        $orderDirection = $params['orderDirection']??'desc';
        $first = $params['first']??10;
        $skip = $params['skip']??0;
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
        $result = [];
        if(!empty($rt['data']) && !empty($rt['data']['transactions'])){
            $userIds = array_column($rt['data']['transactions'], 'user');
            $userIds = array_unique($userIds);
            $userList = UserRepository::getUsersByAddressList($userIds);
            $userMap = [];
            foreach($userList as $user){
                $userMap[$user->address] = $user;
            }
            foreach($rt['data']['transactions'] as $transaction){
                $single = [];
                $single['token'] = $transaction['token'];
                $single['type'] = $transaction['type'];
                $single['tokenAmount'] = $transaction['tokenAmount'];
                $single['transactionHash'] = $transaction['transactionHash'];
                $single['currencyAmount'] = $transaction['metisAmount'];
                $single['userAddress'] = $transaction['user'];
                if(!empty($userMap[$transaction['user']])){
                    $single['userName'] = $userMap[$transaction['user']]->nickName;
                }else{
                    $single['userName'] = $transaction['user'];
                }

                $result[] = $single;
            }
        }
        return $result;
    }

    public function createToken($params): TokenDbModel
    {
       return DB::transaction(function () use ($params) {
            $queryParams = [
                'addressList'=> [$params['address']]
            ];
            $existsToken = TokenRepository::queryTokens($queryParams);
            if(empty($existsToken)){
                $tokenDbModel = $this->createParamsToDbModel($params);
                return TokenRepository::createToken($tokenDbModel);
            }else{
                return $existsToken[0];
            }
        });
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
        return $tokenDbModel;
    }

}
