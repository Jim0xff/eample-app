<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LogRequest
{
    public function handle(Request $request, \Closure $next)
    {

        /**
         * @var Response $rs
         */
        $rs = $next($request);
        //记录请求日志
        try{
            $operator = [];
            if(!is_null(auth()->user())){
                $operator['operatorId'] = auth()->user()->id;
                $operator['operatorName'] = auth()->user()->name;
            }

            \Log::info("requestInfo", [$request->path(), $request->all(), $operator, substr($rs->getContent(),0,1000)]);
        }catch (\Throwable $e){
        }

        return $rs;
    }
}