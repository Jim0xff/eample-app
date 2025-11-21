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
                     print_r($rtTmp);
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
}
