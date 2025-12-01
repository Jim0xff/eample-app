<?php

namespace Pump\Token\Service;

use App\InternalServices\GraphService\Service;
use Carbon\Carbon;
use Pump\Token\Dao\TokenDAOModel;
use Pump\Token\Repository\TokenRepository;

class TradingService
{
     public function getAllTokenTradingVol()
     {
         $idMin = 0;
         $tokens = [];
         /** @var Service $graphService */
         $graphService = resolve(Service::class);
         do{
             $params = [
                 "orderBy" => "id",
                 "orderByDirection" => "asc",
                 "idMin" => $idMin,
             ];
             $tokens = TokenRepository::queryTokens($params);
             $startTime = Carbon::now()->startOfDay()->timestamp;
             $endTime = Carbon::now()->endOfDay()->timestamp;
             if(!empty($tokens)){
                 foreach($tokens as $token){
                    $tokenAddress = $token->address;

                     $graphParams = [
                         "query" => "query MyQuery {
  transactions(where: {token: \"$tokenAddress\", createTimestamp_gte: $startTime, createTimestamp_lt: $endTime}) {
    tokenAmount
    user
    blockNumber
    tokenName
    tokenPrice
    transactionHash
    type
    token
    metisAmount
    id
    from
    createTimestamp
  }
}"
                     ];
                     $rtTmp = $graphService->baseQuery($graphParams);
                     $totalVol = 0;
                     if(!empty($rtTmp['data'] && !empty($rtTmp['data']['transactions']))){
                          foreach($rtTmp['data']['transactions'] as $tokenTransaction){
                              $totalVol += $tokenTransaction['metisAmount'];
                          }
                     }
                     $totalVol = sprintf("%.0f", $totalVol);
                     $tokenSingle = TokenDAOModel::query()->find($token->id);
                     $tokenSingle->trading_volume = $totalVol;
                     $tokenSingle->save();
                     $idMin = $token->id;
                 }
             }
         }while(!empty($tokens));

     }

     public function getContributeUserCnt()
     {
         $idMin = 0;
         $tokens = [];
         /** @var Service $graphService */
         $graphService = resolve(Service::class);
         do{
             $params = [
                 "orderBy" => "id",
                 "orderByDirection" => "asc",
                 "idMin" => $idMin,
             ];
             $tokens = TokenRepository::queryTokens($params);
             $startTime = Carbon::now()->startOfDay()->timestamp;
             $endTime = Carbon::now()->endOfDay()->timestamp;
             if(!empty($tokens)){
                 foreach($tokens as $token){
                     $tokenAddress = $token->address;

                     /** @var \App\InternalServices\CoBuildAgent\CoBuildAgentInternalService $coBuildAgentInternalService */
                     $coBuildAgentInternalService = resolve('co_build_agent_internal_service');

                     $graphParams = [
                         "query" => "query{coBuildUserCount(agentUid:\"$tokenAddress\")}",
                     ];
                     $graphHeaders = [
                         "Authorization"=>"Bearer xVXyLpIV2MS6C6UzpJlf",
                         "x-user-id" => 36
                     ];

                     /** @var \App\InternalServices\CoBuildAgent\CoBuildAgentInternalService $coBuildAgentInternalService */
                     $coBuildAgentInternalService = resolve('co_build_agent_internal_service');
                     $coBuildAgentRt = $coBuildAgentInternalService->agentPost("", $graphParams, $graphHeaders, false);
                     $tokenSingle = TokenDAOModel::query()->find($token->id);
                     if(!empty($coBuildAgentRt['data']) && !empty($coBuildAgentRt['data']['coBuildUserCount'])){
                         $tokenSingle->co_builders = $coBuildAgentRt['data']['coBuildUserCount'];
                     }

                     /** @var $tokenService TokenService */
                     $tokenService = resolve('token_service');
                     $detailParams['tokenId'] = $token->address;
                     $tokenDetail = $tokenService->tokenDetail($detailParams);
                     if(!empty($tokenDetail)){
                         $progress = number_format($tokenDetail['collateral'] / $tokenDetail['fundingGoal'], 5, '.', '');
                         $progress = $progress*100000;
                         $tokenSingle->progress = $progress;
                     }

                     $tokenSingle->save();
                     $idMin = $token->id;


                 }
             }
         }while(!empty($tokens));
     }
}
