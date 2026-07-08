<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class JsonExceptionResolver
{
    public static function statusCode(Throwable $e): int
    {
        return match (get_class($e)) {
            ValidationException::class       => 422,
            AuthenticationException::class   => 401,
            AuthorizationException::class    => 403,
            ModelNotFoundException::class,
            NotFoundHttpException::class     => 404,
            MethodNotAllowedHttpException::class => 405,
            ThrottleRequestsException::class => 429,
            default                          => 500,
        };
    }

    public static function message(Throwable $e, int $status): string
    {
        return match (true) {
            $e instanceof ValidationException     => $e->getMessage() ?: 'Validation failed',
            $e instanceof AuthenticationException => 'Unauthenticated',
            $e instanceof AuthorizationException  => 'Forbidden',
            $e instanceof ModelNotFoundException  => 'Resource not found',
            $e instanceof NotFoundHttpException   => 'Route not found',
            $e instanceof MethodNotAllowedHttpException => 'Method not allowed',
            $e instanceof ThrottleRequestsException     => 'Too many requests',
            $e instanceof QueryException          => 'Database error',
            $status >= 500 && config('app.debug') => $e->getMessage() ?: 'Internal server error',
            $status >= 500                        => 'Internal server error',
            default                               => $e->getMessage() ?: 'Error',
        };
    }

    public static function data(Throwable $e, int $status): mixed
    {
        if ($e instanceof ValidationException) {
            return $e->errors();
        }

        if (config('app.debug')) {
            return null;
        }

        return null;
    }
}
