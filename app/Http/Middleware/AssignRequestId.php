<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AssignRequestId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = (string) Str::uuid();

        Log::shareContext([
            'request-id' => $requestId
        ]);

        $response = $next($request);
        $contentStr = $response->getContent();
        $contentJSON = json_decode($contentStr, true);
        $contentJSON['request-id'] = $requestId;
        $response->setContent(json_encode($contentJSON));
        $response->headers->set('Request-Id', $requestId);

        return $response;
    }
}
