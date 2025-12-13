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
        Route::apiResource('documents', DocumentController::class);
        Route::apiResource('events', EventController::class);
    });

    // Document auto-save endpoint
    Route::patch('/documents/{document}/auto-save', [DocumentController::class, 'autoSave']);

    // Document order endpoint (drag-and-drop)
    Route::patch('/workspaces/{workspace}/documents/order', [DocumentController::class, 'updateOrder']);

    // Calendar events endpoint
    Route::get('/workspaces/{workspace}/calendar/events', [EventController::class, 'getCalendarEvents']);
    
    // Autoguardado de documentos
    Route::post('documents/{document}/autosave', [DocumentController::class, 'autosave']);
    
    // Compartir documento
    Route::post('documents/{document}/share', [DocumentController::class, 'share']);
    
    // Historial de revisiones
    Route::get('documents/{document}/revisions', [DocumentController::class, 'revisions']);
});