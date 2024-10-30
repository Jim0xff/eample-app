<?php

namespace Pump\Utils;

class StrUtils
{

    public static function generateRandomUserName()
    {
       $pre = self::generateRandomString('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', 1);
       $last = self::generateRandomString(null,7);
       return $pre . $last;
    }
    public static function generateRandomString($characters ,$length) {
        if(!$characters){
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        }
        $randomString = '';
        $charLength = strlen($characters);

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, $charLength - 1)];
        }

        return $randomString;
    }
}
