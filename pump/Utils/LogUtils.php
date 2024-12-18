<?php

namespace Pump\Utils;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogUtils
{
    public static function process(Request $request, \Closure $closure)
    {
        $now = microtime(true);
        $user = auth()->user();
        $userUnique = "";
        if(!empty($user)){
            $userUnique = $user->address;
        }else{
            $userUnique = $request->ip();
        }
        try{
            $rt = $closure();
        } finally {
            Log::info($request->url(). " httpMethod:" . $request->method() . " user:$userUnique" . " params: ". json_encode($request->all()) . " costTime:" . (microtime(true) - $now) * 1000);
        }
        return $rt;
    }
}
