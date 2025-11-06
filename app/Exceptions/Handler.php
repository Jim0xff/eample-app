<?php

namespace App\Exceptions;

use App\Http\Responses\ErrorJsonResponse;
use App\InternalServices\DomainException;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\ErrorHandler\Error\FatalError;

class Handler extends ExceptionHandler
{

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
        \App\InternalServices\DomainException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable $exception
     * @return void
     */
    public function report(\Throwable $exception)
    {
        if (app()->environment('production') && app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Throwable $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, \Throwable $exception)
    {
        if(!$exception instanceof (Exception::class)){
            $exception = new Exception($exception->getMessage());
        }

        if ($request->getRequestFormat() == 'json' || $request->expectsJson()) {

            return new ErrorJsonResponse($exception);
        }


        return new ErrorJsonResponse($exception);
       //return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Auth\AuthenticationException $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if (starts_with($request->path(), 'wechat')) {
            return redirect('wechat');
        }


        if (starts_with($request->path(), 'api')) {
            throw new DomainException('未登录', 403);
        }

        return redirect()->guest('login');
    }
}
