<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formats the delivery-result array returned by
 * NotificationService::notifyUsers()/notifyTokens() — both share this
 * exact shape (invalid_tokens present on both; skipped_users_without_token
 * only meaningful for the audience flow, so it's included when present).
 */
class PushResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'success' => $this->resource['success'],
            'failure' => $this->resource['failure'],
            'invalid_tokens' => $this->resource['invalid_tokens'] ?? [],
            'skipped_users_without_token' => $this->resource['skipped_users_without_token'] ?? null,
        ];
    }

    /** Human-readable summary, used as the response's top-level "message". */
    public function summary(): string
    {
        $skipped = $this->resource['skipped_users_without_token'] ?? null;

        $message = "Sent to {$this->resource['success']} device(s), {$this->resource['failure']} failed.";

        if ($skipped !== null) {
            $message = "Sent to {$this->resource['success']} device(s), {$this->resource['failure']} failed, "
                . count($skipped) . ' user(s) had no device registered.';
        }

        return $message;
    }
}
