<?php

use App\Http\Controllers\Api\V1\AdjuntoController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\MensajeController;
use App\Http\Controllers\Api\V1\TerminosController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — /api/v1
|--------------------------------------------------------------------------
*/

// Auth (public)
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('api.v1.auth.login');

// Auth (authenticated)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout'])
        ->name('api.v1.auth.logout');

    Route::get('/me', [AuthController::class, 'me'])
        ->name('api.v1.me');

    // Terms
    Route::get('/terms/current', [TerminosController::class, 'current'])
        ->name('api.v1.terms.current');

    Route::post('/terms/accept', [TerminosController::class, 'accept'])
        ->name('api.v1.terms.accept');

    // Casilla (requires terms accepted)
    Route::middleware('terms.accepted')->prefix('casilla')->group(function () {
        // Messages
        Route::get('/messages', [MensajeController::class, 'index'])
            ->name('api.v1.casilla.messages.index');

        Route::get('/messages/statuses', [MensajeController::class, 'statuses'])
            ->name('api.v1.casilla.messages.statuses');

        Route::get('/messages/{id}', [MensajeController::class, 'show'])
            ->name('api.v1.casilla.messages.show');

        Route::patch('/messages/{id}/read', [MensajeController::class, 'markRead'])
            ->name('api.v1.casilla.messages.markRead');

        Route::patch('/messages/{id}/archive', [MensajeController::class, 'archive'])
            ->name('api.v1.casilla.messages.archive');

        // Attachments
        Route::get('/messages/{messageId}/attachments', [AdjuntoController::class, 'index'])
            ->name('api.v1.casilla.messages.attachments');

        Route::get('/attachments/{id}/download', [AdjuntoController::class, 'download'])
            ->middleware('throttle:30,1')
            ->name('api.v1.casilla.attachments.download');
    });
});
