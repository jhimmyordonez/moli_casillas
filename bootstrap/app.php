<?php

use App\Http\Middleware\EnsureTermsAccepted;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'terms.accepted' => EnsureTermsAccepted::class,
            'casilla.activa' => \App\Http\Middleware\EnsureCasillaActiva::class,
        ]);

        $middleware->statefulApi();
        $middleware->validateCsrfTokens(except: ['api/*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'data' => null,
                    'meta' => null,
                    'errors' => [
                        ['code' => 'UNAUTHENTICATED', 'message' => $e->getMessage() ?: 'Unauthenticated.'],
                    ],
                ], 401);
            }
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'data' => null,
                    'meta' => null,
                    'errors' => [
                        ['code' => 'FORBIDDEN', 'message' => $e->getMessage() ?: 'Forbidden.'],
                    ],
                ], 403);
            }
        });

        $exceptions->render(function (NotFoundHttpException|ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'data' => null,
                    'meta' => null,
                    'errors' => [
                        ['code' => 'NOT_FOUND', 'message' => 'Resource not found.'],
                    ],
                ], 404);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $errors = collect($e->errors())->map(function ($messages, $field) {
                    return [
                        'code' => 'VALIDATION_ERROR',
                        'field' => $field,
                        'message' => $messages[0],
                    ];
                })->values()->all();

                return response()->json([
                    'data' => null,
                    'meta' => null,
                    'errors' => $errors,
                ], 422);
            }
        });

        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'data' => null,
                    'meta' => null,
                    'errors' => [
                        ['code' => 'TOO_MANY_REQUESTS', 'message' => 'Too many requests. Please try again later.'],
                    ],
                ], 429);
            }
        });
    })->create();
