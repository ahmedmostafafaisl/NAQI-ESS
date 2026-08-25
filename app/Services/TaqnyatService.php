<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class TaqnyatService
{
    public function sendOtp(string $mobile, string $otp, string $locale = 'en'): array
    {
        $message = __('api.dynamics_otp.sms_body', ['otp' => $otp], $locale);

        return $this->sendSms($mobile, $message);
    }

    /**
     * @return array{success:bool, error:?string, raw:array}
     */
    public function sendSms(string $mobile, string $message): array
    {
        $recipient = $this->normalizeSaudiMobile($mobile);

        if (! $recipient) {
            return ['success' => false, 'error' => "Invalid mobile number: {$mobile}", 'raw' => []];
        }

        try {
            $response = Http::withToken(config('services.taqnyat.bearer_token'))
                ->timeout(config('services.taqnyat.timeout'))
                ->post(config('services.taqnyat.base_url'), [
                    'recipients' => [$recipient],
                    'body' => $message,
                    'sender' => config('services.taqnyat.sender_name'),
                ]);
        } catch (\Throwable $e) {
            Log::error('Taqnyat: SMS request threw an exception', ['mobile' => $recipient, 'error' => $e->getMessage()]);

            return ['success' => false, 'error' => 'Could not reach Taqnyat: ' . $e->getMessage(), 'raw' => []];
        }

        $body = $response->json() ?? [];

        if ($response->failed()) {
            Log::error('Taqnyat: SMS send failed', ['mobile' => $recipient, 'status' => $response->status(), 'body' => $body]);

            return [
                'success' => false,
                'error' => $body['message'] ?? $body['error'] ?? "Taqnyat rejected the request (HTTP {$response->status()}).",
                'raw' => $body,
            ];
        }

        return ['success' => true, 'error' => null, 'raw' => $body];
    }

    /**
     * Normalize a Saudi mobile number to the international format Taqnyat
     * expects (966XXXXXXXXX, no leading zero, no plus sign). Returns null
     * if the number doesn't look like a valid Saudi mobile at all.
     */
    protected function normalizeSaudiMobile(string $mobile): ?string
    {
        $digits = preg_replace('/\D/', '', $mobile);

        if (str_starts_with($digits, '00966')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            $digits = '966' . substr($digits, 1);
        } elseif (str_starts_with($digits, '5') && strlen($digits) === 9) {
            $digits = '966' . $digits;
        }

        // Expect 966 + 9 digits (starting with 5) = 12 digits total.
        if (! preg_match('/^9665\d{8}$/', $digits)) {
            return null;
        }

        return $digits;
    }
}
