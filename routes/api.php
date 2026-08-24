<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DynamicsController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PushBroadcastController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');
        Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
        Route::post('resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:3,1');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:6,1');
        Route::post('dynamics-login', [AuthController::class, 'dynamicsLogin'])->middleware('throttle:6,1');
        Route::post('dynamics-verify-otp', [AuthController::class, 'verifyDynamicsOtp'])->middleware('throttle:10,1');
        Route::post('login-pin', [AuthController::class, 'loginWithPin'])->middleware('throttle:6,1');
        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
        Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:10,1');
    });

    // Public: no auth required. Only settings explicitly flagged is_public are exposed here.
    Route::get('settings/public', [SettingController::class, 'publicIndex']);

    // Public pass-through Dynamics 365 endpoints — identity is proven by the
    // Dynamics session `token` in the body (from auth/dynamics-login), not by
    // our own Sanctum guard. Throttled to limit abuse of an unauthenticated route.
    Route::prefix('dynamics')->group(function () {
        Route::post('team-members', [DynamicsController::class, 'teamMembers'])->middleware('throttle:20,1');
        Route::post('attendance-calendar', [DynamicsController::class, 'attendanceCalendar'])->middleware('throttle:20,1');
        Route::post('attendance-record', [DynamicsController::class, 'attendanceRecord'])->middleware('throttle:20,1');
        Route::post('home-page', [DynamicsController::class, 'homePage'])->middleware('throttle:6,1');
        Route::post('requests', [DynamicsController::class, 'allRequests'])->middleware('throttle:20,1');
        Route::post('request-detail', [DynamicsController::class, 'requestDetail'])->middleware('throttle:20,1');
        Route::post('workers-directory', [DynamicsController::class, 'workersDirectory'])->middleware('throttle:20,1');
        Route::post('vacation-types', [DynamicsController::class, 'vacationTypes'])->middleware('throttle:20,1');
        Route::post('vacations', [DynamicsController::class, 'createVacation'])->middleware('throttle:10,1');
        Route::post('vacations/cancel', [DynamicsController::class, 'cancelVacation'])->middleware('throttle:10,1');
        Route::post('excuse-types', [DynamicsController::class, 'excuseTypes'])->middleware('throttle:20,1');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/change-password', [AuthController::class, 'changePassword']);
        Route::post('auth/set-pin', [AuthController::class, 'setPin']);
        Route::post('auth/fcm-token', [AuthController::class, 'updateFcmToken']);
        Route::get('profile', [AuthController::class, 'profile']);
        Route::post('profile', [AuthController::class, 'updateProfile']);

        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('unread-count', [NotificationController::class, 'unreadCount']);
            Route::post('{id}/read', [NotificationController::class, 'markAsRead']);
            Route::post('read-all', [NotificationController::class, 'markAllAsRead']);
            Route::delete('{id}', [NotificationController::class, 'destroy']);
        });

        // Two ways to broadcast a push: resolve an audience of Users, or push to raw tokens directly.
        Route::post('push/send-to-audience', [PushBroadcastController::class, 'sendToAudience'])
            ->middleware('permission:notifications.send')
            ->middleware('throttle:20,1');
        Route::post('push/send-to-tokens', [PushBroadcastController::class, 'sendToTokens'])
            ->middleware('permission:notifications.send')
            ->middleware('throttle:20,1');

        Route::prefix('settings')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->middleware('permission:settings.view');
            Route::get('{key}', [SettingController::class, 'show'])->middleware('permission:settings.view');
            Route::post('/', [SettingController::class, 'store'])->middleware('permission:settings.manage');
            Route::put('{key}', [SettingController::class, 'update'])->middleware('permission:settings.manage');
            Route::delete('{key}', [SettingController::class, 'destroy'])->middleware('permission:settings.manage');
        });
    });
});
