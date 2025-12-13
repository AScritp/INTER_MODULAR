<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Document auto-save endpoint
    Route::patch('/documents/{document}/auto-save', [DocumentController::class, 'autoSave']);

    // Document order endpoint (drag-and-drop)
    Route::patch('/workspaces/{workspace}/documents/order', [DocumentController::class, 'updateOrder']);

    // Calendar events endpoint
    Route::get('/workspaces/{workspace}/calendar/events', [EventController::class, 'getCalendarEvents']);
});
