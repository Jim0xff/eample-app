<?php
namespace App\Http\Responses;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ErrorJsonResponse extends JsonResponse
{

    public function __construct(Exception $e)
    {
        parent::__construct($this->envelop($e), self::HTTP_OK);
        $this->withException($e);
    }

    protected function envelop(Exception $e)
    {
        return config('app.debug') ? $this->envelopDebug($e) : $this->envelopNormal($e);
    }

    protected function envelopNormal(Exception $e)
    {
        if ($e instanceof HttpException) {
            return $this->wrap($e->getStatusCode(), self::$statusTexts[$e->getStatusCode()]);
        }
        if ($e instanceof HttpResponseException) {
            return $this->wrap($e->getResponse()->getStatusCode(), $e->getResponse()->getContent());
        }
        if ($e instanceof AuthorizationException) {
            return $this->wrap(self::HTTP_FORBIDDEN, trans('exceptions.authorization'));
        }
        if ($e instanceof ModelNotFoundException) {
            $model = trans("models.{$e->getModel()}");
            return $this->wrap(self::HTTP_NOT_FOUND, trans('exceptions.model_not_found', ['model' => $model]));
        }
        if ($e instanceof AuthenticationException) {
            return $this->wrap(self::HTTP_UNAUTHORIZED, trans('exceptions.unauthenticated'));
        }
        if ($e instanceof ValidationException) {
            $messages = $e->validator->getMessageBag()->all();
            return $this->wrap(self::HTTP_UNPROCESSABLE_ENTITY, implode("\n", $messages));
        }
        if ($e instanceof \PDOException) {
            if (!env('APP_DEBUG')) {
                return $this->wrap(self::HTTP_INSUFFICIENT_STORAGE, trans('exceptions.pdo'));
            }

        }

        return $this->wrap($e->getCode(), $e->getMessage());
    }

    protected function envelopDebug(Exception $e)
    {
        return array_merge($this->envelopNormal($e), [
            'trace' => explode("\n", $e->getTraceAsString()),
        ]);
    }

    protected function wrap($code, $message)
    {
        return ['code' => $code ?: 500, 'resultMessage' => $message ?: trans('exceptions.unknown')];
    }

}
