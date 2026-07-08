<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Scenario 2: send a push notification directly to a raw list of FCM device
 * tokens, with no dependency on those tokens belonging to a User record.
 * Intended for external systems / internal tools that already have tokens
 * on hand and just want to push a message.
 */
class PushBroadcastController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

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
