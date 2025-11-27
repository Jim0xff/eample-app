<?php

namespace App\InternalServices\Coingecko;

use App\InternalServices\AbstractService;
use Illuminate\Support\Facades\Redis;

class CoingeckoService extends AbstractService
{
    public static $TOKEN_PRICE_PRE_KEY = 'token_price_pre_key2_';
     public function getTokenPrice($tokenApiCodes, $currency, $expireSeconds = 300)
     {
         $redis = Redis::connection();
         $cacheData = $redis->get(self::$TOKEN_PRICE_PRE_KEY . $tokenApiCodes . "_" . $currency);
         if(!empty($cacheData)){
             return json_decode($cacheData, true);
         }
         $param =[
             "ids" => $tokenApiCodes,
             "vs_currencies" => $currency
         ];
         $rt =$this->getDataWithHeaders("api/v3/simple/price",
             [
                 "headers"=>[
                     "accept" => 'application/json',
                     "x-cg-pro-api-key" => config('internal.coingecko_api_key'),
                 ],
                 "query"=>$param
             ]);
         //$rt = $this->getData("api/v3/simple/price", $param);

         $redis->command('set',[self::$TOKEN_PRICE_PRE_KEY . $tokenApiCodes . "_" . $currency, json_encode($rt), 'EX', $expireSeconds]);
         return $rt;
     }
}
