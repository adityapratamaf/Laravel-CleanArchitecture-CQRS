<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use App\Support\Helpers\ApiResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: base_path('app/Presentation/Routes/web.php'),
        api: base_path('app/Presentation/Routes/api.php'),
        commands: base_path('routes/console.php'),
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware Jika Web Iddle Selama 30 Menit, Maka Logout
        $middleware->alias([
            'idle.timeout' => \App\Http\Middleware\AuthenticateSessionIdleTimeout::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(
                    ApiResponse::error(
                        message: 'validation error',
                        code: 422,
                        errors: $e->errors()
                    ),
                    422
                );
            }
        });
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(
                    ApiResponse::error(
                        message: 'unauthenticated',
                        code: 401,
                        errors: null
                    ),
                    401
                );
            }
        });
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(
                    ApiResponse::error(
                        message: 'route not found',
                        code: 404,
                        errors: null
                    ),
                    404
                );
            }
        });
        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(
                    ApiResponse::error(
                        message: 'method not allowed',
                        code: 405,
                        errors: null
                    ),
                    405
                );
            }
        });
        $exceptions->render(function (\DomainException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(
                    ApiResponse::error(
                        message: $e->getMessage() ?: 'domain error',
                        code: 422,
                        errors: null
                    ),
                    422
                );
            }
        });
        $exceptions->render(function (HttpExceptionInterface $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $status = $e->getStatusCode();

                return response()->json(
                    ApiResponse::error(
                        message: $e->getMessage() ?: 'http error',
                        code: $status,
                        errors: null
                    ),
                    $status
                );
            }
        });
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(
                    ApiResponse::error(
                        message: app()->isProduction() ? 'server error' : $e->getMessage(),
                        code: 500,
                        errors: app()->isProduction() ? null : [
                            'exception' => get_class($e),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ]
                    ),
                    500
                );
            }
        });
    })->create();
