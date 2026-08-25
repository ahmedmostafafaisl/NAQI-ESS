<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\IndexNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    public function index(IndexNotificationRequest $request): JsonResponse
    {
        $feed = $this->notifications->feedFor(
            notifiable: $request->user(),
            category: $request->validated('category'),
            perPage: ApiResponse::perPage($request),
            page: (int) $request->input(ApiResponse::PAGE_NAME, 1),
            pageName: ApiResponse::PAGE_NAME,
        );

        return ApiResponse::paginated($feed->through(fn(DatabaseNotification $n) => new NotificationResource($n)));
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return ApiResponse::success(['unread_count' => $this->notifications->unreadCountFor($request->user())]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $this->notifications->markAsReadFor($request->user(), $id);

        return ApiResponse::success([], 'Marked as read.');
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->notifications->markAllAsReadFor($request->user());

        return ApiResponse::success([], 'All notifications marked as read.');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->notifications->deleteFor($request->user(), $id);

        return ApiResponse::success([], 'Notification deleted.');
    }
}
