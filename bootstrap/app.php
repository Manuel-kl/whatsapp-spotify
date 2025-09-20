<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $isProduction = app()->isProduction();
        $exceptions->render(function (Throwable $e) use ($isProduction) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $statusCode = $isProduction ? 401 : 404;

                return response()->json([
                    'message' => $isProduction ? 'Unauthorized' : 'Resource not found.',
                    'error' => $isProduction ? 'Unauthorized' : $e->getMessage(),
                ], $statusCode);
            }

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'message' => 'Validation error.',
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json([
                    'message' => 'Unauthorized.',
                ], 403);
            }

            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return response()->json([
                    'message' => $isProduction ? 'Unauthorized' : 'Resource not found',
                    'error' => $isProduction ? 'Unauthorized' : $e->getMessage(),
                ], 404);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                $statusCode = $isProduction ? 401 : 405;

                return response()->json([
                    'message' => $isProduction ? 'Unauthorized' : 'Method not allowed.',
                    'error' => $isProduction ? 'Unauthorized' : 'Allowed method is '.implode(', ', $e->getHeaders()),
                ], $statusCode);
            }

            if ($e instanceof \Illuminate\Database\QueryException) {
                $statusCode = $isProduction ? 401 : 500;

                return response()->json([
                    'message' => $isProduction ? 'Unauthorized' : 'Db query error.',
                    'error' => $isProduction ? 'Unauthorized' : $e->getMessage(),
                ], $statusCode);
            }

            if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                return response()->json([
                    'message' => 'Too many requests. Please try again later.',
                ], 429);
            }

            if ($e instanceof AccessDeniedHttpException) {
                return response()->json([
                    'message' => 'This action is unauthorized.',
                ], 403);
            }

            if ($e instanceof \GuzzleHttp\Exception\RequestException) {
                return response()->json([
                    'message' => 'HTTP request error.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            if ($e instanceof \GuzzleHttp\Exception\ConnectException) {
                return response()->json([
                    'message' => 'Network issue, failed to connect.',
                ], 500);
            }
        });
    })->create();
