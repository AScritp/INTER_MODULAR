<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WorkspaceController;

// IMPORTANTE: Cambiar auth:web por auth:sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Workspaces
    Route::apiResource('workspaces', WorkspaceController::class);

    // Documentos dentro de workspace
    Route::prefix('workspaces/{workspace}')->group(function () {
        // Rutas custom ANTES de apiResource para evitar conflictos
        Route::patch('/documents/order', [DocumentController::class, 'updateOrder']);
        Route::get('/calendar/events', [EventController::class, 'getCalendarEvents']);

        Route::apiResource('documents', DocumentController::class);
        Route::apiResource('events', EventController::class);
    });

    // Document auto-save endpoint
    Route::patch('/documents/{document}/auto-save', [DocumentController::class, 'autoSave']);

    // Autoguardado de documentos
    Route::post('documents/{document}/autosave', [DocumentController::class, 'autosave']);

    // Compartir documento
    Route::post('documents/{document}/share', [DocumentController::class, 'share']);

    // Historial de revisiones
    Route::get('documents/{document}/revisions', [DocumentController::class, 'revisions']);
});