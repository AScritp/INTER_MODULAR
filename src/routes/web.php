<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('workspaces.index');
    }
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/workspaces', [WorkspaceController::class, 'getWorkspacesJson'])->name('workspaces.json');
        Route::post('/workspaces', [WorkspaceController::class, 'storeJson'])->name('workspaces.store.json');
        Route::delete('/workspaces/{workspace}', [WorkspaceController::class, 'destroyJson'])->name('workspaces.destroy.json');
        Route::get('/workspaces/{workspace}/documents', [DocumentController::class, 'getDocumentsJson'])->name('documents.json');
        Route::post('/workspaces/{workspace}/documents', [DocumentController::class, 'storeJson'])->name('documents.store.json');
        Route::delete('/workspaces/{workspace}/documents/{document}', [DocumentController::class, 'destroyJson'])->name('documents.destroy.json');
    });

    // Workspaces
    Route::resource('workspaces', WorkspaceController::class);
    Route::post('workspaces/{workspace}/users', [WorkspaceController::class, 'addUser'])
        ->name('workspaces.addUser');
    Route::delete('workspaces/{workspace}/users/{user}', [WorkspaceController::class, 'removeUser'])
        ->name('workspaces.removeUser');
    Route::patch('workspaces/{workspace}/users/{user}', [WorkspaceController::class, 'updateUserRole'])
        ->name('workspaces.updateUserRole');

    // Documents
    Route::resource('documents', DocumentController::class)->except(['index', 'create', 'store']);
    Route::get('workspaces/{workspace}/documents', [DocumentController::class, 'index'])
        ->name('documents.index');
    Route::get('workspaces/{workspace}/documents/create', [DocumentController::class, 'create'])
        ->name('documents.create');
    Route::post('workspaces/{workspace}/documents', [DocumentController::class, 'store'])
        ->name('documents.store');
    Route::patch('documents/{document}/auto-save', [DocumentController::class, 'autoSave'])
        ->name('documents.autoSave');
    Route::patch('workspaces/{workspace}/documents/order', [DocumentController::class, 'updateOrder'])
        ->name('documents.updateOrder');
    Route::patch('documents/{document}/restore', [DocumentController::class, 'restore'])
        ->name('documents.restore');

    // Events
    Route::get('workspaces/{workspace}/events', [EventController::class, 'index'])
        ->name('events.index');
    Route::get('workspaces/{workspace}/calendar', [EventController::class, 'calendar'])
        ->name('events.calendar');
    Route::get('workspaces/{workspace}/calendar/events', [EventController::class, 'getCalendarEvents'])
        ->name('events.getCalendarEvents');
    Route::get('workspaces/{workspace}/events/create', [EventController::class, 'create'])
        ->name('events.create');
    Route::post('workspaces/{workspace}/events', [EventController::class, 'store'])
        ->name('events.store');
    Route::get('events/{event}/edit', [EventController::class, 'edit'])
        ->name('events.edit');
    Route::patch('events/{event}', [EventController::class, 'update'])
        ->name('events.update');
    Route::delete('events/{event}', [EventController::class, 'destroy'])
        ->name('events.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
