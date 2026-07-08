<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Dynamics365Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(protected Dynamics365Service $dynamics) {}

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

        if (! $user || $user->otp !== $request->otp || $user->otp_expires_at?->isPast()) {
            return $this->error('Invalid or expired OTP.', 422);
        }

        $user->forceFill([
            'otp' => null,
            'otp_expires_at' => null,
            'email_verified_at' => now(),
        ])->save();

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

        if (! $user || $user->otp !== $request->otp || $user->otp_expires_at?->isPast()) {
            return $this->error('Invalid or expired OTP.', 422);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'otp' => null,
            'otp_expires_at' => null,
        ])->save();

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

    protected function success($data = [], string $message = '', int $code = 200): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $code);
    }

    protected function error(string $message, int $code = 400): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, 'data' => []], $code);
    }
}
