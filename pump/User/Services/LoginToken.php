<?php

namespace Pump\User\Services;

use Illuminate\Support\Facades\Redis;
use Pump\Utils\Crypt;

class LoginToken
{
    public static $LOGIN_TOKEN_PRE_KEY = 'login_token_on_';
    public function saveToken($address, $valueStr, $expiredSeconds = 3600 * 24 * 7, $type = 1)
    {
        $token = Crypt::cryptEncode($valueStr);
        $redis = Redis::connection();
        $oldToken = $redis->get(self::$LOGIN_TOKEN_PRE_KEY . $address);
        if($oldToken){
            return $oldToken;
        }
        $redis->command('set',[self::$LOGIN_TOKEN_PRE_KEY . $address, $token, 'EX', $expiredSeconds]);
        return $token;
    }

    public function validateToken($token)
    {
        $token = stripslashes($token);
        $token = str_replace(' ', '+', $token);
        $tokenArr = Crypt::cryptDecode($token);

        if (empty($tokenArr['address'])) {
            return null;
        }
        $address = $tokenArr['address'];
        $address = strtolower($address);
        $redis = resolve('redis');
        $serverToken = $redis->command('get', [self::$LOGIN_TOKEN_PRE_KEY . $address]);
        if (empty($token) || empty($serverToken) || $token != $serverToken) {
            $tokenArr = null;
        }
        return $tokenArr;
    }

    public function expiredToken($address)
    {
        $redis = resolve('redis');
        $redis->command('del', [self::$LOGIN_TOKEN_PRE_KEY . $address]);
    }
}
