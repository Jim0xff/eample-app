<?php

namespace App\InternalServices\LazpadTaskService;

use App\InternalServices\AbstractService;
use App\InternalServices\DomainException;
use Illuminate\Support\Facades\Redis;

class LazpadTaskService extends AbstractService
{
     public static $DECODE_TOKEN_CACHE_KEY = "decode_token_cache_key_";
    public static $USER_ID_TO_INFO = "user_id_to_info_";

     public function decodeToken($token)
     {
         $redis = Redis::connection();
         $cacheRt = $redis->get(self::$DECODE_TOKEN_CACHE_KEY . $token );
         if($cacheRt){
             return json_decode($cacheRt, true);
         }
         $decodeRt = $this->getDataWithHeaders("user/decodeToken", [
             "headers"=>[
                 "Authorization" => "Bearer ".$token,
                 "traceId"=> app('requestId')
             ],
             "query"=>[
                 "token"=>$token
             ]
         ]);

         if($decodeRt['code'] != 200){
             throw new DomainException("auth failed", "403");
         }

         $userInfo = $decodeRt['data']['userInfo'];
         $userInfo['address'] = $userInfo['ethAddress'];
         $redis->command('set',[self::$DECODE_TOKEN_CACHE_KEY . $token , json_encode($userInfo), 'EX', 300]);
         $redis->command('set',[self::$USER_ID_TO_INFO . $userInfo['id'] , json_encode($userInfo)]);

         return $userInfo;
     }
}
