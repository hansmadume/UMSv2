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
    Route::middleware(['permission:tickets.view'])->group(function () {
        Route::get('/support', [SupportController::class, 'dashboard'])->name('support.dashboard');
        Route::get('/support/tickets', [SupportController::class, 'index'])->name('support.index');
        Route::get('/support/tickets/create', [SupportController::class, 'create'])->name('support.create');
        Route::post('/support/tickets', [SupportController::class, 'store'])->name('support.store');
    });

    Route::middleware(['permission:tickets.view_own'])->group(function () {
        Route::get('/support/my-tickets', [SupportController::class, 'myTickets'])->name('support.my-tickets');
    });

    Route::middleware(['auth', 'inactivity'])->group(function () {
        Route::get('/support/tickets/{ticket}', [SupportController::class, 'show'])->name('support.show');
        Route::put('/support/tickets/{ticket}', [SupportController::class, 'update'])->name('support.update');
    });

    Route::middleware(['permission:support.contact'])->group(function () {
        Route::post('/contact-support', [SupportController::class, 'storeContact'])->name('support.contact.store');
    });

    // Secure file download for support attachments
    Route::middleware(['auth', 'inactivity'])->group(function () {
        Route::get('/support/tickets/{ticket}/attachments/{attachment}', [SupportController::class, 'downloadAttachment'])->name('support.attachments.download');
    });

    // User Management
    Route::middleware(['permission:users.view'])->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    });

    // Roles Management
    Route::middleware(['permission:roles.view'])->group(function () {
        Route::resource('roles', RoleController::class);
    });

    // Permissions Management
    Route::middleware(['permission:permissions.view'])->group(function () {
        Route::resource('permissions', PermissionController::class);
    });

    // Audit Logs
    Route::middleware(['permission:audit_logs.view'])->group(function () {
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});

require __DIR__.'/auth.php';
