<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

/**
 * Thin wrapper around Kreait's Firebase Messaging client.
 *
 * Two entry points, matching the two real-world scenarios:
 *  - sendToUsers()  : resolve tokens from App\Models\User records (dashboard flow)
 *  - sendToTokens() : send directly to a raw list of device tokens (integration flow)
 */
class FcmService
{
    public function __construct(protected Messaging $messaging) {}

    /**
     * Send a push notification to a collection/array of Users that have an fcm_token.
     * Automatically drops users without a token and reports which ones were skipped.
     *
     * @param  iterable<User>  $users
     */
    public function sendToUsers(iterable $users, string $title, string $body, array $data = []): array
    {
        $tokens = [];
        $skipped = [];

        foreach ($users as $user) {
            if (! empty($user->fcm_token)) {
                $tokens[$user->fcm_token] = $user->id;
            } else {
                $skipped[] = $user->id;
            }
        }

        $result = $this->sendToTokens(array_keys($tokens), $title, $body, $data);
        $result['skipped_users_without_token'] = $skipped;

        return $result;
    }

    /**
     * Send a push notification to a raw list of FCM device tokens.
     * Not tied to any User record — used for the "paste tokens directly" scenario.
     *
     * @param  string[]  $tokens
     * @return array{success:int, failure:int, invalid_tokens:string[]}
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        $tokens = array_values(array_unique(array_filter($tokens)));

        if (empty($tokens)) {
            return ['success' => 0, 'failure' => 0, 'invalid_tokens' => []];
        }

        // FCM's multicast endpoint accepts at most 500 tokens per request.
        $chunks = array_chunk($tokens, 500);

        $success = 0;
        $failure = 0;
        $invalidTokens = [];

        foreach ($chunks as $chunk) {
            $message = CloudMessage::new()
                ->withNotification(FcmNotification::create($title, $body))
                ->withData(array_map('strval', $data));

            try {
                /** @var MulticastSendReport $report */
                $report = $this->messaging->sendMulticast($message, $chunk);

                $success += $report->successes()->count();
                $failure += $report->failures()->count();

                foreach ($report->invalidTokens() as $invalidToken) {
                    $invalidTokens[] = $invalidToken;
                }
            } catch (\Throwable $e) {
                Log::error('FCM multicast send failed', ['error' => $e->getMessage()]);
                $failure += count($chunk);
            }
        }

        return [
            'success' => $success,
            'failure' => $failure,
            'invalid_tokens' => $invalidTokens,
        ];
    }
}
