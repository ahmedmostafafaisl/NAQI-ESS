<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Push\SendToAudienceRequest;
use App\Http\Requests\Push\SendToTokensRequest;
use App\Http\Resources\PushResultResource;
use App\Services\NotificationService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * Two ways to broadcast a push notification via the API:
 *
 *  - sendToAudience(): resolve a group of app Users (all / employees / customers /
 *    a specific list of user IDs) and notify them — writes an in-app notification
 *    record for each AND pushes via FCM to whichever ones have a device token.
 *
 *  - sendToTokens(): push directly to a raw list of FCM device tokens, with no
 *    dependency on those tokens belonging to a User record. No in-app record is
 *    created. Intended for external systems that already have tokens on hand.
 */
class PushBroadcastController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    public function sendToAudience(SendToAudienceRequest $request): JsonResponse
    {
        $data = $request->validated();

        $users = $this->notifications->resolveAudience($data['audience'], $data['user_ids'] ?? []);

        $result = $this->notifications->notifyUsers(
            users: $users,
            title: $data['title'],
            body: $data['body'],
            category: $data['category'] ?? 'system',
            data: $data['data'] ?? [],
            sender: $request->user(),
        );

        $resource = new PushResultResource($result);

        return ApiResponse::success($resource, $resource->summary());
    }

    public function sendToTokens(SendToTokensRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->notifications->notifyTokens(
            tokens: $data['tokens'],
            title: $data['title'],
            body: $data['body'],
            data: $data['data'] ?? [],
        );

        $resource = new PushResultResource($result);

        return ApiResponse::success($resource, $resource->summary());
    }
}
