<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DynamicsLoginResource;
use App\Models\DynamicsUser;
use App\Models\User;
use App\Services\Dynamics365Service;
use App\Services\TaqnyatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(protected Dynamics365Service $dynamics, protected TaqnyatService $taqnyat) {}

    /** Register a new employee/customer account. */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'type' => ['nullable', 'in:employee,customer'],
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $otp = $this->generateOtp();

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'type' => $request->type ?? 'employee',
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes((int) config('otp.expires_minutes', 5)),
        ]);

        $user->assignRole('employee');

        // TODO: dispatch SMS/email job with $otp

        return $this->success([
            'user_id' => $user->id,
            'otp_sent_to' => $user->phone,
        ], 'Registered successfully. Please verify the OTP sent to your phone.');
    }

    /** Verify OTP sent at registration (or resent via resendOtp). */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'otp' => ['required', 'string'],
        ]);

        $user = User::where('phone', $request->phone)->first();

        $otpMatches = $user
            && ($user->otp === $request->otp || $this->matchesDefaultOtp($request->otp))
            && ! $user->otp_expires_at?->isPast();

        if (! $otpMatches) {
            return $this->error('Invalid or expired OTP.', 422);
        }

        $user->forceFill([
            'otp' => null,
            'otp_expires_at' => null,
            'email_verified_at' => now(),
        ])->save();

        $user->tokens()->delete(); // single active session across all devices
        $token = $user->createToken('naqi-ess')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => $user,
        ], 'Account verified successfully.');
    }

    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate(['phone' => ['required', 'string']]);

        $user = User::where('phone', $request->phone)->firstOrFail();
        $otp = $this->generateOtp();

        $user->forceFill([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes((int) config('otp.expires_minutes', 5)),
        ])->save();

        // TODO: dispatch SMS/email job with $otp

        return $this->success([], 'OTP resent successfully.');
    }

    /** Login with phone/username/email + password. */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login' => ['required', 'string'], // phone, username, or email
            'password' => ['required', 'string'],
        ]);

        $user = User::where('phone', $request->login)
            ->orWhere('username', $request->login)
            ->orWhere('email', $request->login)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error('Invalid credentials.', 401);
        }

        if ($user->status !== 'active') {
            return $this->error('Your account has been deactivated.', 403);
        }

        $user->tokens()->delete(); // single active session; drop this line to allow multiple devices
        $token = $user->createToken('naqi-ess')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => $user->load('roles'),
        ], 'Login successful.');
    }

    /**
     * Login by authenticating against Dynamics 365 F&O's custom Login service
     * (INDXNaqiEssAuthSvc/Login) rather than the local password hash. On
     * success, the local User record (matched/created by email) is synced
     * with the HR context Dynamics returns (Worker -> personnel_number,
     * IsManager, the Dynamics session token) and a normal Sanctum token is
     * issued for this app's own API, same as the regular login endpoint.
     */
    public function dynamicsLogin(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_token' => ['nullable', 'string'],
            'lang' => ['nullable', 'string', 'in:en-us,ar-sa,en,ar'],
            'app_version' => ['nullable', 'string'],
            'device_platform' => ['nullable', 'string', 'in:android,ios'],
        ]);

        $locale = $this->resolveApiLocale($request->lang);

        $result = $this->dynamics->loginUser(
            email: $request->email,
            password: $request->password,
            deviceToken: $request->device_token ?? '',
            lang: $request->lang,
            appVersion: $request->app_version ?? '',
            devicePlatform: $request->device_platform ?? '',
        );

        if (! $result['success']) {
            return $this->error($result['error'], 401);
        }

        if (empty($result['mobile'])) {
            return $this->error(__('api.dynamics_otp.no_mobile', [], $locale), 422);
        }

        // Local record for this login flow only (separate from the app's own
        // `users` table) — update-or-create keyed on email. Only overwrite
        // device_token when one was actually sent, so a login without it
        // doesn't wipe out a previously registered device.
        $otp = $this->generateOtp();
        $otpExpiresAt = now()->addMinutes((int) config('otp.expires_minutes', 5));

        $attributes = [
            'password' => $request->password,
            'mobile' => $result['mobile'],
            'otp' => $otp,
            'otp_expires_at' => $otpExpiresAt,
        ];

        if ($request->filled('device_token')) {
            $attributes['device_token'] = $request->device_token;
        }

        DynamicsUser::updateOrCreate(['email' => $request->email], $attributes);

        // Cache the RAW Dynamics envelope (not a normalized subset) so
        // verifyDynamicsOtp() below can format it through the exact same
        // DynamicsLoginResource that dynamics-login itself uses — one
        // source of truth for the response shape, not two.
        cache()->put(
            "dynamics_pending_login:{$request->email}",
            $result['raw'],
            $otpExpiresAt,
        );


        // only for test
        return $this->success(
            new DynamicsLoginResource($result['raw']),
            __('api.dynamics_otp.sent', [], $locale),
        );

        $sms = $this->taqnyat->sendOtp($result['mobile'], $otp, $locale);

        if (! $sms['success']) {
            return $this->error(__('api.dynamics_otp.send_failed', ['error' => $sms['error']], $locale), 502);
        }

        return $this->success(
            new DynamicsLoginResource($result['raw']),
            __('api.dynamics_otp.sent', [], $locale),
        );
    }

    /**
     * Second step of the Dynamics login flow: verify the OTP sent to the
     * user's mobile, then release the actual Dynamics session data that
     * dynamicsLogin() withheld.
     */
    public function verifyDynamicsOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string'],
        ]);

        $dynamicsUser = DynamicsUser::where('email', $request->email)->first();

        $isRealOtp = $dynamicsUser
            && $dynamicsUser->otp === $request->otp
            && $dynamicsUser->otp_expires_at
            && ! $dynamicsUser->otp_expires_at->isPast();

        // The default/testing OTP bypasses expiry entirely — it's meant to
        // always work for QA/app-store review, not just within whatever
        // window happens to be left on a real code that may have already
        // expired. It still requires an actual pending Dynamics session to
        // exist below, though — it only skips the SMS code check.
        $isDefaultOtp = $this->matchesDefaultOtp($request->otp);

        if (! $dynamicsUser || (! $isRealOtp && ! $isDefaultOtp)) {
            return $this->error('Invalid or expired verification code.', 422);
        }

        $pending = cache()->get("dynamics_pending_login:{$request->email}");

        if (! $pending) {
            return $this->error('Your session has expired — please log in again.', 422);
        }

        // Consume the OTP so it can't be reused, and drop the cached session.
        $dynamicsUser->forceFill(['otp' => null, 'otp_expires_at' => null])->save();
        cache()->forget("dynamics_pending_login:{$request->email}");

        return $this->success(new DynamicsLoginResource($pending), 'Login successful.');
    }

    /**
     * Resends the Dynamics login OTP without asking for the password again.
     * Only works while the pending session from Step 1 (dynamicsLogin) is
     * still cached — if it's already expired, there's no session left to
     * eventually release, so the user is told to log in again from scratch
     * rather than getting a code that would lead nowhere.
     */
    public function resendDynamicsOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'lang' => ['nullable', 'string', 'in:en-us,ar-sa,en,ar'],
        ]);

        $locale = $this->resolveApiLocale($request->lang);

        $dynamicsUser = DynamicsUser::where('email', $request->email)->first();

        if (! $dynamicsUser || ! $dynamicsUser->mobile) {
            return $this->error(__('api.dynamics_otp.no_pending_login', [], $locale), 422);
        }

        $pending = cache()->get("dynamics_pending_login:{$request->email}");

        if (! $pending) {
            return $this->error(__('api.dynamics_otp.session_expired', [], $locale), 422);
        }

        $otp = $this->generateOtp();
        $otpExpiresAt = now()->addMinutes((int) config('otp.expires_minutes', 5));

        $dynamicsUser->forceFill([
            'otp' => $otp,
            'otp_expires_at' => $otpExpiresAt,
        ])->save();

        // Extend the cached session so it still lines up with the new OTP's expiry.
        cache()->put("dynamics_pending_login:{$request->email}", $pending, $otpExpiresAt);

        $sms = $this->taqnyat->sendOtp($dynamicsUser->mobile, $otp, $locale);

        if (! $sms['success']) {
            return $this->error(__('api.dynamics_otp.resend_failed', ['error' => $sms['error']], $locale), 502);
        }

        return $this->success([
            'mobile_masked' => $this->maskMobile($dynamicsUser->mobile),
        ], __('api.dynamics_otp.resent', [], $locale));
    }

    /**
     * Normalizes any incoming lang value ("ar", "ar-sa", "en", "en-us", or
     * missing) down to just "ar" or "en" for picking a translation file —
     * matches on the language prefix so callers don't need to send the
     * exact locale code the validation rule happens to list.
     */
    protected function resolveApiLocale(?string $lang): string
    {
        return str_starts_with(strtolower((string) $lang), 'ar') ? 'ar' : 'en';
    }

    protected function maskMobile(string $mobile): string
    {
        $digits = preg_replace('/\D/', '', $mobile);
        $tail = substr($digits, -2);

        return str_repeat('*', max(0, strlen($digits) - 2)) . $tail;
    }

    /** Login with a numeric PIN, e.g. for quick kiosk/mobile access. */
    public function loginWithPin(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'pin_code' => ['required', 'string'],
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (! $user || ! $user->pin_code || ! Hash::check($request->pin_code, $user->pin_code)) {
            return $this->error('Invalid phone number or PIN.', 401);
        }

        $user->tokens()->delete(); // single active session across all devices
        $token = $user->createToken('naqi-ess')->plainTextToken;

        return $this->success(['token' => $token, 'user' => $user], 'Login successful.');
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['phone' => ['required', 'string']]);

        $user = User::where('phone', $request->phone)->firstOrFail();
        $otp = $this->generateOtp();

        $user->forceFill([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes((int) config('otp.expires_minutes', 5)),
        ])->save();

        // TODO: dispatch SMS/email job with $otp

        return $this->success([], 'OTP sent to reset your password.');
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'otp' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::where('phone', $request->phone)->first();

        $otpMatches = $user
            && ($user->otp === $request->otp || $this->matchesDefaultOtp($request->otp))
            && ! $user->otp_expires_at?->isPast();

        if (! $otpMatches) {
            return $this->error('Invalid or expired OTP.', 422);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'otp' => null,
            'otp_expires_at' => null,
        ])->save();

        $user->tokens()->delete(); // a password reset invalidates any existing sessions

        return $this->success([], 'Password reset successfully.');
    }

    public function profile(Request $request): JsonResponse
    {
        return $this->success($request->user()->load('roles', 'permissions'));
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'username' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'unique:users,email,' . $request->user()->id],
            'image' => ['sometimes', 'image', 'max:4096'],
        ]);

        $user = $request->user();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('users', 'public');
            $user->image = $path;
        }

        $user->fill($request->only(['username', 'email']))->save();

        return $this->success($user, 'Profile updated successfully.');
    }

    public function updateFcmToken(Request $request): JsonResponse
    {
        $request->validate(['fcm_token' => ['required', 'string']]);
        $request->user()->update(['fcm_token' => $request->fcm_token]);

        return $this->success([], 'FCM token updated.');
    }

    /** Set or change the quick-access PIN. Requires the account password to confirm identity. */
    public function setPin(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
            'pin_code' => ['required', 'string', 'digits_between:4,6'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->password, $user->password)) {
            return $this->error('Password is incorrect.', 422);
        }

        $user->update(['pin_code' => $request->pin_code]); // auto-hashed via the model cast

        return $this->success([], 'PIN updated successfully.');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return $this->error('Current password is incorrect.', 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        // Revoke every other session but keep the one making this request
        // alive — the user is actively using it right now.
        $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return $this->success([], 'Password changed successfully.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success([], 'Logged out successfully.');
    }

    protected function generateOtp(): string
    {
        return (string) random_int(
            (int) str_pad('1', (int) config('otp.length', 4), '0'),
            (int) str_pad('9', (int) config('otp.length', 4), '9')
        );
    }

    /**
     * Whether the given code matches the configured testing/QA default OTP
     * (see config/otp.php). Deliberately double-guarded: requires BOTH an
     * explicitly configured value AND a non-production environment, so a
     * production .env accidentally carrying OTP_DEFAULT_CODE still can't
     * activate a universal login bypass.
     */
    protected function matchesDefaultOtp(string $submittedOtp): bool
    {
        $defaultOtp = config('otp.default_otp');
        $allowedEnvironments = config('otp.default_otp_environments', []);

        if (empty($defaultOtp) || ! app()->environment($allowedEnvironments)) {
            return false;
        }

        return hash_equals((string) $defaultOtp, $submittedOtp);
    }

    protected function success($data = [], string $message = '', int $code = 200): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $code);
    }

    protected function error(string $message, int $code = 400): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, 'data' => []], $code);
    }

    /**
     * Standalone test utility: sends a real OTP via Taqnyat to the given
     * phone number, with no user lookup and nothing persisted — purely for
     * verifying the SMS gateway itself works, independent of registration
     * or any other flow. Returns Taqnyat's raw response so delivery issues
     * are visible directly rather than hidden behind a generic message.
     *
     * SECURITY: this sends a real, billed SMS to any phone number handed to
     * it, with no auth and no rate-limit tie to an actual account — that's
     * an abuse/cost vector if left reachable in production. Remove this
     * route (or gate it behind auth + a stricter throttle) before shipping.
     */
    public function testSendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        $otp = $this->generateOtp();
        $result = $this->taqnyat->sendOtp($request->phone, $otp);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success']
                ? "Test OTP ({$otp}) sent to {$request->phone}."
                : $result['error'],
            'data' => [
                'otp_sent' => $otp,
                'taqnyat_response' => $result['raw'],
            ],
        ], $result['success'] ? 200 : 502);
    }
}
