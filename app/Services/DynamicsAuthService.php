<?php

namespace App\Services;

use App\Models\DynamicsUser;
use App\Repositories\Contracts\DynamicsUserRepositoryInterface;
use App\Services\Concerns\ResolvesDefaultOtp;

class DynamicsAuthService
{
    use ResolvesDefaultOtp;

    public function __construct(
        protected Dynamics365Service $dynamics,
        protected TaqnyatService $taqnyat,
        protected DynamicsUserRepositoryInterface $dynamicsUsers,
    ) {}


    public function login(string $email, string $password, ?string $deviceToken, ?string $lang, ?string $appVersion, ?string $devicePlatform, ?string $resource = null): array
    {
        $result = $this->dynamics->loginUser(
            email: $email,
            password: $password,
            deviceToken: $deviceToken ?? '',
            lang: $lang,
            appVersion: $appVersion ?? '',
            devicePlatform: $devicePlatform ?? '',
            resource: $resource,
        );

        if (! $result['success']) {
            return $this->failure('dynamics_rejected', $result['error']);
        }

        if (empty($result['mobile'])) {
            return $this->failure('no_mobile');
        }

        $otp = $this->generateOtp();
        $otpExpiresAt = now()->addMinutes((int) config('otp.expires_minutes', 5));

        $attributes = [
            'password' => $password,
            'mobile' => $result['mobile'],
            'otp' => $otp,
            'otp_expires_at' => $otpExpiresAt,
        ];

        if ($deviceToken) {
            $attributes['device_token'] = $deviceToken;
        }

        $this->dynamicsUsers->updateOrCreate($email, $attributes);

        // Cache the RAW Dynamics envelope so verifyOtp() below can format it
        // through the exact same DynamicsLoginResource this method's caller
        // uses — one source of truth for the response shape, not two.
        cache()->put("dynamics_pending_login:{$email}", $result['raw'], $otpExpiresAt);
        return ['success' => true, 'error_code' => null, 'error' => null, 'raw' => $result['raw']];

        $sms = $this->taqnyat->sendOtp($result['mobile'], $otp, $this->resolveLocale($lang));

        if (! $sms['success']) {
            return $this->failure('send_failed', $sms['error']);
        }

        return ['success' => true, 'error_code' => null, 'error' => null, 'raw' => $result['raw']];
    }

    /**
     * @return array{success:bool, error_code:?string, error:?string, raw:?array}
     */
    public function verifyOtp(string $email, string $otp): array
    {
        $dynamicsUser = $this->dynamicsUsers->findByEmail($email);

        $isRealOtp = $dynamicsUser
            && $dynamicsUser->otp === $otp
            && $dynamicsUser->otp_expires_at
            && ! $dynamicsUser->otp_expires_at->isPast();

        // The default/testing OTP bypasses expiry entirely — always
        // available for QA/app-store review, not just within whatever
        // window happens to be left on a real code. Still requires an
        // actual pending Dynamics session to exist below, though — it
        // only skips the SMS-code check.
        $isDefaultOtp = $this->matchesDefaultOtp($otp);

        if (! $dynamicsUser || (! $isRealOtp && ! $isDefaultOtp)) {
            return $this->failure('invalid_otp');
        }

        $pending = cache()->get("dynamics_pending_login:{$email}");

        if (! $pending) {
            return $this->failure('session_expired');
        }

        $dynamicsUser->forceFill(['otp' => null, 'otp_expires_at' => null])->save();
        cache()->forget("dynamics_pending_login:{$email}");

        return ['success' => true, 'error_code' => null, 'error' => null, 'raw' => $pending];
    }

    /**
     * @return array{success:bool, error_code:?string, error:?string, mobile:?string}
     */
    public function resendOtp(string $email, ?string $lang): array
    {
        $dynamicsUser = $this->dynamicsUsers->findByEmail($email);

        if (! $dynamicsUser || ! $dynamicsUser->mobile) {
            return $this->failure('no_pending_login');
        }

        $pending = cache()->get("dynamics_pending_login:{$email}");

        if (! $pending) {
            return $this->failure('session_expired');
        }

        $otp = $this->generateOtp();
        $otpExpiresAt = now()->addMinutes((int) config('otp.expires_minutes', 5));

        $dynamicsUser->forceFill(['otp' => $otp, 'otp_expires_at' => $otpExpiresAt])->save();

        // Extend the cached session so it still lines up with the new OTP's expiry.
        cache()->put("dynamics_pending_login:{$email}", $pending, $otpExpiresAt);

        $sms = $this->taqnyat->sendOtp($dynamicsUser->mobile, $otp, $this->resolveLocale($lang));

        if (! $sms['success']) {
            return $this->failure('resend_failed', $sms['error']);
        }

        return ['success' => true, 'error_code' => null, 'error' => null, 'mobile' => $dynamicsUser->mobile];
    }

    public function maskMobile(string $mobile): string
    {
        $digits = preg_replace('/\D/', '', $mobile);
        $tail = substr($digits, -2);

        return str_repeat('*', max(0, strlen($digits) - 2)) . $tail;
    }

    protected function failure(string $code, ?string $detail = null): array
    {
        return ['success' => false, 'error_code' => $code, 'error' => $detail, 'raw' => null];
    }

    protected function generateOtp(): string
    {
        return (string) random_int(
            (int) str_pad('1', (int) config('otp.length', 4), '0'),
            (int) str_pad('9', (int) config('otp.length', 4), '9')
        );
    }

    protected function resolveLocale(?string $lang): string
    {
        return str_starts_with(strtolower((string) $lang), 'ar') ? 'ar' : 'en';
    }
}
