<?php

namespace App\Http\Middleware;


use App\InternalServices\DomainException;
use Illuminate\Http\Request;

class ApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
//        auth()->shouldUse('api');
        /** @var \Illuminate\Auth\TokenGuard $guard */
        $guard = auth()->guard();
        if (!$guard->check()) {
            throw new DomainException('not login', 401);
        }

        return $next($request);
    }
}
