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
  userMeMeTokenTransactions(where: {token: $tokenAddress, createTimestamp_gte: $startTime, createTimestamp_lt: $endTime}) {
    blockNumber
    createTimestamp
    from
    id
    to
    token
    tokenAmount
    transactionHash
  }
}"
                     ];
                     $rtTmp = $graphService->baseQuery($graphParams);
                     $totalVol = 0;
                     if(!empty($rtTmp['data'] && !empty($rtTmp['data']['userMeMeTokenTransactions']))){
                          foreach($rtTmp['data']['userMeMeTokenTransactions'] as $tokenTransaction){
                              $totalVol += $tokenTransaction['tokenAmount'];
                          }
                     }
                     $totalVol = sprintf("%.0f", $totalVol);
                     $tokenSingle = TokenDAOModel::query()->find($token->id);
                     $tokenSingle->trading_volume = $token;
                     TokenDAOModel::query()->update($tokenSingle);
                     $idMin = $token->id;
                 }
             }
         }while(!empty($tokens));

     }
}
