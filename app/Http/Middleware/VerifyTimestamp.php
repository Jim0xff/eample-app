<?php

namespace App\Http\Middleware;

use App\InternalServices\DomainException;
use Closure;


class VerifyTimestamp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */


    public function handle($request, Closure $next,$timestampKey =null)
    {
        if(!env('APP_DEBUG')){
            $time = trim($request->input($timestampKey));

            if (!$time) {
                throw new DomainException('The request is missing timestamp parameter,Check the parameter.', 403);
            };

            //若接口请求时间与当前时间相差10分钟，提示失效
            $diffTime = time() - $time;

            if ($diffTime >= 600) {
                throw new DomainException('The request has expired.', 401);
            }
        }

        return $next($request);
    }

}
