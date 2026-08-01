<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function sendToAudience(Request $request): JsonResponse
    {
        $data = $request->validate([
            'audience' => ['required', 'in:all,employees,customers,specific'],
            'user_ids' => ['required_if:audience,specific', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'data' => ['sometimes', 'array'],
        ]);

        $query = match ($data['audience']) {
            'employees' => User::where('type', 'employee'),
            'customers' => User::where('type', 'customer'),
            'specific' => User::whereIn('id', $data['user_ids'] ?? []),
            default => User::query(),
        };

        $result = $this->notifications->notifyUsers(
            users: $query->active()->get(),
            title: $data['title'],
            body: $data['body'],
            category: $data['category'] ?? 'system',
            data: $data['data'] ?? [],
            sender: $request->user(),
        );

        return response()->json([
            'success' => true,
            'message' => "Sent to {$result['success']} device(s), {$result['failure']} failed, "
                . count($result['skipped_users_without_token']) . ' user(s) had no device registered.',
            'data' => $result,
        ]);
    }

    public function sendToTokens(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'tokens' => ['required', 'array', 'min:1', 'max:1000'],
            'tokens.*' => ['string'],
            'data' => ['sometimes', 'array'],
        ]);

        $result = $this->notifications->notifyTokens(
            tokens: $data['tokens'],
            title: $data['title'],
            body: $data['body'],
            data: $data['data'] ?? [],
        );

        return response()->json([
            'success' => true,
            'message' => "Sent to {$result['success']} device(s), {$result['failure']} failed.",
            'data' => $result,
        ]);
    }
}
