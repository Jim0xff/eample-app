<?php

namespace Pump\Token\Service;

use App\InternalServices\Airdrop\AirdropService;
use App\InternalServices\GraphService\Service;
use App\InternalServices\LazpadTaskService\LazpadTaskService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use Pump\Brick\InternalCall\Dao\InternalCallRetryDAOModel;
use Pump\Token\Repository\TokenRepository;

class TokenGraduateService
{

    public static $SCAN_TRADING_TOKEN_UPDATE_START_KEY = 'scan_trading_token_update_start_key';

    public function scanTradingToken()
    {
        $start = Carbon::now()->startOfDay();

        $end = Carbon::now()->endOfDay();
        /** @var Service $graphService */
        $graphService = resolve(Service::class);
        $orderBy = 'updateTimestamp';
        $orderDirection = 'asc';
        $redis = Redis::connection();
        $updateTimestampStart = $redis->get(self::$SCAN_TRADING_TOKEN_UPDATE_START_KEY);
        if (empty($updateTimestampStart)) {
            $updateTimestampStart = 0;
        }
        $whereStr = "{status: \"TRADING\", updateTimestamp_gt: \"$updateTimestampStart\"}";
        $graphParams = [
            "query" => "query MyQuery {
  tokens(where: $whereStr
         orderBy: $orderBy
         orderDirection: $orderDirection
         first: 0
         skip: 100
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

        if (!empty($rt['data']) && !empty($rt['data']['tokens'])) {

            /** @var AirdropService $airdropService */
            $airdropService = resolve('airdrop_service');
            $tokensIdsRt = array_column($rt['data']['tokens'], 'id');
            $dbModels = TokenRepository::queryTokens(['addressList'=>$tokensIdsRt]);
            $dbModelsMap = [];

            if(!empty($dbModels)){
                foreach($dbModels as $dbModel){
                    $dbModelsMap[$dbModel->address] = $dbModel;
                }
            }
            foreach ($rt['data']['tokens'] as $token) {

                $dobModel = $dbModelsMap[$token->id];
                $dbContent = $dobModel->content;
                if(!empty($dbContent["airdropActivityId"]) && $dobModel->airdropRate){
                    $contributeUsers = $this->getTokenContributesUser($token['id'], $dbModel->coBuildAgentId);
                    if(!empty($contributeUsers)){
                        $internalCallRetryDaoModel = new InternalCallRetryDaoModel();
                        $internalCallRetryDaoModel->biz_id = $dbContent["airdropActivityId"];
                        $internalCallRetryDaoModel->biz_type = "airdrop_activity";
                        $internalCallRetryDaoModel->status = "INIT";
                        $internalCallContent = [];
                        $internalCallContent["airdropActivityId"] = $dbContent["airdropActivityId"];
                        $internalCallContent["token"] = $token;
                        $internalCallContent["contributeUsers"] = $contributeUsers;
                        $internalCallRetryDaoModel->content = json_encode($internalCallContent);
                        $internalCallRetryDaoModel->save();
                        try{
                            $this->doInsertAirdrop($contributeUsers, $dbContent["airdropActivityId"]);
                        }
                        catch(\Throwable $e){
                            \Log::error(sprintf(
                                "batch insert airdrop record failed, activityId: %s ",
                                $dbContent["airdropActivityId"]
                            ));
                            $internalCallRetryDaoModel->status = "ERROR";
                            $internalCallRetryDaoModel->save();
                        }
                        $internalCallRetryDaoModel->status = "SUCCESS";
                        $internalCallRetryDaoModel->save();
                    }
                }
                $redis->command('set',[self::$SCAN_TRADING_TOKEN_UPDATE_START_KEY, $token['updateTimestamp']]);

            }
        }
    }

    public function retryInsertAirdrop()
    {
        $records = InternalCallRetryDaoModel::query()
            ->where("status", "!=", "SUCCESS")
            ->where("created_at" , "<=", Carbon::now()->subMinutes(10))
            ->limit(100)
            ->get();
        if(!empty($records)){
            foreach($records as $record){
                $contentObj = json_decode($record->content, true);
                $contributeUsers = $contentObj["contributeUsers"];
                $airdropActivityId = $contentObj["airdropActivityId"];
                try{
                    $this->doInsertAirdrop($contributeUsers, $airdropActivityId);
                }catch (\Throwable $e){
                    \Log::error(sprintf(
                        "batch insert airdrop record failed, activityId: %s ",
                        $airdropActivityId
                    ));
                    continue;
                }
                $record->status = "SUCCESS";
                $record->save();
            }
        }
    }

    private function doInsertAirdrop($contributeUsers, $airdropActivityId)
    {
        /** @var AirdropService $airdropService */
        $airdropService = resolve('airdrop_service');
        foreach ($contributeUsers as $contributeUser) {
            try{
                $airdropService->airdropPost('insert-airdrop-record',
                    [
                        "userAddress" => $contributeUser['address'],
                        "activityId" => $airdropActivityId,
                        "amount" => $contributeUser['airdropAmount'],
                    ],
                    [
                        "apiToken" => config("internal.airdrop_service_api_key")
                    ]);
            }catch (\Throwable $e){
                \Log::error(sprintf(
                    "insert airdrop record failed, activityId: %s  userInfo: %s ",
                    $airdropActivityId,
                    json_encode($contributeUser, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                ));
                throw $e;
            }
        }
    }

    private function getTokenContributesUser($tokenAddress, $agentId)
    {
        $redis = Redis::connection();
        $result = [];
        $userIds = ["34"];
        foreach ($userIds as $userId) {
            $userInfo = json_decode($redis->get(LazpadTaskService::$USER_ID_TO_INFO. $userId), true);
            $userInfo['airdropAmount'] = "1000000000000";
            $result[] = $userInfo;
        }

        return $result;
    }

}
