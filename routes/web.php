<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AdminAuthController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin/login');

Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AdminAuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware(['auth', 'role:admin|super-admin'])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class)->except('show');
        Route::resource('permissions', PermissionController::class)->only(['index', 'store', 'destroy']);

        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('create', [NotificationController::class, 'create'])->name('create');
            Route::post('/', [NotificationController::class, 'store'])->name('store');
            Route::post('{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        });
    });
});
