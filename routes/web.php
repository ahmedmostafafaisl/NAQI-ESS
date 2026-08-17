<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DynamicsAttendanceController;
use App\Http\Controllers\Admin\DynamicsController;
use App\Http\Controllers\Admin\DynamicsWorkspaceController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin/login');

Route::get('locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AdminAuthController::class, 'login'])->name('login.attempt')->middleware('throttle:6,1');
    });

    Route::middleware(['auth', 'role:admin|super-admin'])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class)->except('show');
        Route::resource('permissions', PermissionController::class)->only(['index', 'store', 'destroy']);
        Route::resource('settings', SettingController::class)->except('show');

        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('create', [NotificationController::class, 'create'])->name('create');
            Route::post('/', [NotificationController::class, 'store'])->name('store');
            Route::post('{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        });

        Route::prefix('dynamics')->name('dynamics.')->group(function () {
            Route::get('/', [DynamicsController::class, 'index'])->name('index');
            Route::post('test', [DynamicsController::class, 'testConnection'])->name('test')->middleware('throttle:10,1');
            Route::post('test-login', [DynamicsController::class, 'testUserLogin'])->name('test-login')->middleware('throttle:10,1');
            Route::post('test-team-members', [DynamicsController::class, 'testTeamMembers'])->name('test-team-members')->middleware('throttle:10,1');
            Route::post('test-home-page', [DynamicsController::class, 'testHomePage'])->name('test-home-page')->middleware('throttle:10,1');
            Route::post('test-all-requests', [DynamicsController::class, 'testAllRequests'])->name('test-all-requests')->middleware('throttle:10,1');
            Route::post('request-detail', [DynamicsController::class, 'requestDetail'])->name('request-detail')->middleware('throttle:30,1');
            Route::post('test-workers-directory', [DynamicsController::class, 'testWorkersDirectory'])->name('test-workers-directory')->middleware('throttle:10,1');
            Route::post('test-vacation-types', [DynamicsController::class, 'testVacationTypes'])->name('test-vacation-types')->middleware('throttle:10,1');
            Route::post('test-create-vacation', [DynamicsController::class, 'testCreateVacation'])->name('test-create-vacation')->middleware('throttle:10,1');
            Route::post('vacation-types-lookup-ajax', [DynamicsController::class, 'vacationTypesLookupAjax'])->name('vacation-types-lookup-ajax')->middleware('throttle:10,1');
            Route::post('test-cancel-vacation', [DynamicsController::class, 'testCancelVacation'])->name('test-cancel-vacation')->middleware('throttle:10,1');
            Route::post('test-excuse-types', [DynamicsController::class, 'testExcuseTypes'])->name('test-excuse-types')->middleware('throttle:10,1');

            Route::prefix('attendance')->name('attendance.')->group(function () {
                Route::get('/', [DynamicsAttendanceController::class, 'index'])->name('index');
                Route::post('calendar', [DynamicsAttendanceController::class, 'calendar'])->name('calendar')->middleware('throttle:10,1');
                Route::post('day', [DynamicsAttendanceController::class, 'day'])->name('day')->middleware('throttle:30,1');
            });

            Route::prefix('workspace')->name('workspace.')->group(function () {
                Route::get('/', [DynamicsWorkspaceController::class, 'index'])->name('index');
                Route::post('login', [DynamicsWorkspaceController::class, 'login'])->name('login')->middleware('throttle:6,1');
                Route::post('select-member', [DynamicsWorkspaceController::class, 'selectMember'])->name('select-member');
                Route::post('back-to-team', [DynamicsWorkspaceController::class, 'backToTeam'])->name('back-to-team');
                Route::post('calendar', [DynamicsWorkspaceController::class, 'calendar'])->name('calendar')->middleware('throttle:10,1');
                Route::post('day', [DynamicsWorkspaceController::class, 'day'])->name('day')->middleware('throttle:30,1');
                Route::post('logout', [DynamicsWorkspaceController::class, 'logout'])->name('logout');
            });
        });
    });
});
