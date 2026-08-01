<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->when($request->category, fn($q) => $q->where('data->category', $request->category))
            ->latest()
            ->paginate(
                perPage: ApiResponse::perPage($request),
                pageName: ApiResponse::PAGE_NAME,
            );

        return ApiResponse::paginated($notifications);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return ApiResponse::success(['unread_count' => $request->user()->unreadNotifications()->count()]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return ApiResponse::success([], 'Marked as read.');
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return ApiResponse::success([], 'All notifications marked as read.');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $request->user()->notifications()->findOrFail($id)->delete();

        return ApiResponse::success([], 'Notification deleted.');
    }
}
