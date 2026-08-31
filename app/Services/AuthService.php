<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Repositories\Contracts\UserDeviceRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Concerns\ResolvesDefaultOtp;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    use ResolvesDefaultOtp;

    /** Setting key toggling whether old devices get notified/logged-out when a new device verifies OTP. Enabled by default if the setting doesn't exist yet. */
    protected const NOTIFY_OLD_DEVICES_SETTING_KEY = 'notify_old_devices_on_login';

    public function __construct(
        protected UserRepositoryInterface $users,
        protected UserDeviceRepositoryInterface $devices,
        protected NotificationService $notifications,
        protected SettingRepositoryInterface $settings,
    ) {}

    public function register(array $data): User
    {
        $user = $this->users->create([
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'type' => $data['type'] ?? 'employee',
            'otp' => $this->generateOtp(),
            'otp_expires_at' => now()->addMinutes((int) config('otp.expires_minutes', 5)),
        ]);

        // NOTE: preserved exactly as it was before this refactor — this
        // always assigns 'employee', even when type is 'customer'. That
        // looks like a pre-existing oversight, not something intentional,
        // but fixing it wasn't part of this migration's scope. Flagging
        // rather than silently changing behavior — worth a decision on
        // whether $user->type should drive this instead.
        $user->assignRole('employee');

        // TODO: dispatch SMS/email job with the OTP

        return $user;
    }

    /** @return array{success:bool, error_code:?string, user:?User} */
    public function verifyOtp(string $phone, string $otp, ?string $deviceId = null, ?string $fcmToken = null): array
    {
        $user = $this->users->findByPhone($phone);

        $otpMatches = $user
            && ($user->otp === $otp || $this->matchesDefaultOtp($otp))
            && ! $user->otp_expires_at?->isPast();

        if (! $otpMatches) {
            return ['success' => false, 'error_code' => 'invalid_or_expired_otp', 'user' => null];
        }

        $user->forceFill([
            'otp' => null,
            'otp_expires_at' => null,
            'email_verified_at' => now(),
        ])->save();

        $this->revokeAllSessions($user);

        if ($deviceId && $fcmToken) {
            $this->registerDeviceAndNotifyOthers($user, $deviceId, $fcmToken);
        }

        return ['success' => true, 'error_code' => null, 'user' => $user];
    }

    public function resendOtp(string $phone): ?User
    {
        $user = $this->users->findByPhone($phone);

        if (! $user) {
            return null;
        }

        $user->forceFill([
            'otp' => $this->generateOtp(),
            'otp_expires_at' => now()->addMinutes((int) config('otp.expires_minutes', 5)),
        ])->save();

        // TODO: dispatch SMS/email job with the OTP

        return $user;
    }

    /** @return array{success:bool, error_code:?string, user:?User} */
    public function login(string $login, string $password, ?string $deviceId = null, ?string $fcmToken = null): array
    {
        $user = $this->users->findByLoginField($login);

        if (! $user || ! Hash::check($password, $user->password)) {
            return ['success' => false, 'error_code' => 'invalid_credentials', 'user' => null];
        }

        if ($user->status !== 'active') {
            return ['success' => false, 'error_code' => 'deactivated', 'user' => null];
        }

        $this->revokeAllSessions($user);

        if ($deviceId && $fcmToken) {
            $this->devices->upsert($user, $deviceId, $fcmToken);
        }

        return ['success' => true, 'error_code' => null, 'user' => $user];
    }

    /** @return array{success:bool, error_code:?string, user:?User} */
    public function loginWithPin(string $phone, string $pinCode): array
    {
        $user = $this->users->findByPhone($phone);

        if (! $user || ! $user->pin_code || ! Hash::check($pinCode, $user->pin_code)) {
            return ['success' => false, 'error_code' => 'invalid_pin', 'user' => null];
        }

        $this->revokeAllSessions($user);

        return ['success' => true, 'error_code' => null, 'user' => $user];
    }

    public function forgotPassword(string $phone): ?User
    {
        $user = $this->users->findByPhone($phone);

        if (! $user) {
            return null;
        }

        $user->forceFill([
            'otp' => $this->generateOtp(),
            'otp_expires_at' => now()->addMinutes((int) config('otp.expires_minutes', 5)),
        ])->save();

        // TODO: dispatch SMS/email job with the OTP

        return $user;
    }

    /** @return array{success:bool, error_code:?string} */
    public function resetPassword(string $phone, string $otp, string $newPassword): array
    {
        $user = $this->users->findByPhone($phone);

        $otpMatches = $user
            && ($user->otp === $otp || $this->matchesDefaultOtp($otp))
            && ! $user->otp_expires_at?->isPast();

        if (! $otpMatches) {
            return ['success' => false, 'error_code' => 'invalid_or_expired_otp'];
        }

        $user->forceFill([
            'password' => Hash::make($newPassword),
            'otp' => null,
            'otp_expires_at' => null,
        ])->save();

        // A password reset invalidates any existing sessions.
        $this->revokeAllSessions($user);

        return ['success' => true, 'error_code' => null];
    }

    /** @return array{success:bool, error_code:?string} */
    public function changePassword(User $user, string $currentPassword, string $newPassword, int|string $currentTokenId): array
    {
        if (! Hash::check($currentPassword, $user->password)) {
            return ['success' => false, 'error_code' => 'incorrect_current_password'];
        }

        $user->update(['password' => Hash::make($newPassword)]);

        // Revoke every other session but keep the one making this request
        // alive — the user is actively using it right now.
        $user->tokens()->where('id', '!=', $currentTokenId)->delete();

        return ['success' => true, 'error_code' => null];
    }

    /** @return array{success:bool, error_code:?string} */
    public function setPin(User $user, string $password, string $pinCode): array
    {
        if (! Hash::check($password, $user->password)) {
            return ['success' => false, 'error_code' => 'incorrect_password'];
        }

        $user->update(['pin_code' => $pinCode]); // auto-hashed via the model cast

        return ['success' => true, 'error_code' => null];
    }

    public function updateFcmToken(User $user, string $fcmToken): void
    {
        $user->update(['fcm_token' => $fcmToken]);
    }

    /** Standalone device registration — used by the dedicated add-device endpoint. */
    public function registerDevice(User $user, string $deviceId, string $fcmToken): void
    {
        $this->devices->upsert($user, $deviceId, $fcmToken);
    }

    /**
     * Registers the device that just verified its OTP, then notifies every
     * OTHER device this user has registered — the new device's ID is
     * embedded in the notification's data payload so the receiving devices
     * can show something like "signed in on [device]" if they want to.
     */
    protected function registerDeviceAndNotifyOthers(User $user, string $deviceId, string $fcmToken): void
    {
        $this->devices->upsert($user, $deviceId, $fcmToken);

        if (! $this->notifyOldDevicesEnabled()) {
            return;
        }

        // Only the 5 most recently active OTHER devices, not every device
        // this user has ever registered — those 5 are the ones being told
        // to log out.
        $recentOtherTokens = $this->devices->recentTokensExcept($user, exceptDeviceId: $deviceId, limit: 5);

        if (empty($recentOtherTokens)) {
            return;
        }

        $this->notifications->notifyTokens(
            tokens: $recentOtherTokens,
            title: 'New device signed in',
            body: 'Your account was just verified on a new device.',
            data: ['type' => 'logout', 'new_device_id' => $deviceId],
        );
    }

    /**
     * Whether notifying old devices (and telling them to log out) is turned
     * on — a real Setting (admin-manageable via the existing Settings
     * module), not a config value, so this can be toggled without a
     * deploy. Defaults to enabled if the setting hasn't been created yet.
     */
    protected function notifyOldDevicesEnabled(): bool
    {
        $setting = $this->settings->findByKey(self::NOTIFY_OLD_DEVICES_SETTING_KEY);

        return $setting ? (bool) $setting->cast_value : true;
    }

    public function logout(User $user, int|string $currentTokenId): void
    {
        $user->tokens()->where('id', $currentTokenId)->delete();
    }

    /** Single active session across all devices — revoked on every fresh login/registration/reset. */
    public function revokeAllSessions(User $user): void
    {
        $user->tokens()->delete();
    }

    public function generateOtp(): string
    {
        return (string) random_int(
            (int) str_pad('1', (int) config('otp.length', 4), '0'),
            (int) str_pad('9', (int) config('otp.length', 4), '9')
        );
    }
}
