<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// Authenticated routes
Route::middleware(['auth', 'inactivity'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'avatar'])->name('profile.avatar');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Support
    Route::middleware(['role:Administrator,Support Staff'])->group(function () {
        Route::get('/support', [SupportController::class, 'dashboard'])->name('support.dashboard');
        Route::get('/support/tickets', [SupportController::class, 'index'])->name('support.index');
        Route::get('/support/my-tickets', [SupportController::class, 'myTickets'])->name('support.my-tickets');
        Route::get('/support/tickets/create', [SupportController::class, 'create'])->name('support.create');
        Route::post('/support/tickets', [SupportController::class, 'store'])->name('support.store');
    });

    Route::middleware(['auth', 'inactivity'])->group(function () {
        Route::get('/support/tickets/{ticket}', [SupportController::class, 'show'])->name('support.show');
        Route::put('/support/tickets/{ticket}', [SupportController::class, 'update'])->name('support.update');
    });

    Route::post('/contact-support', [SupportController::class, 'storeContact'])->name('support.contact.store');

    // User Management - administrator and manager
    Route::middleware(['role:Administrator,Manager'])->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    });

    // Roles Management - administrator only
    Route::middleware(['role:Administrator'])->group(function () {
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
    });

    // Audit Logs - administrator only
    Route::middleware(['role:Administrator'])->group(function () {
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});

require __DIR__.'/auth.php';
