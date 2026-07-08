<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->when($request->category, fn ($q) => $q->where('data->category', $request->category))
            ->latest()
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $notifications]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ['unread_count' => $request->user()->unreadNotifications()->count()],
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true, 'message' => 'Marked as read.']);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true, 'message' => 'All notifications marked as read.']);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $request->user()->notifications()->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Notification deleted.']);
    }
}
