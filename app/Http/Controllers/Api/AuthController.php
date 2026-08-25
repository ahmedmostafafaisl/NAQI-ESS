<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\DynamicsLoginRequest;
use App\Http\Requests\Auth\DynamicsResendOtpRequest;
use App\Http\Requests\Auth\DynamicsVerifyOtpRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginPinRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SetPinRequest;
use App\Http\Requests\Auth\UpdateFcmTokenRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Resources\DynamicsLoginResource;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\DynamicsAuthService;
use App\Services\TaqnyatService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $auth,
        protected DynamicsAuthService $dynamicsAuth,
        protected TaqnyatService $taqnyat,
        protected UserService $users,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->auth->register($request->validated());

        return $this->success([
            'user_id' => $user->id,
            'otp_sent_to' => $user->phone,
        ], 'Registered successfully. Please verify the OTP sent to your phone.');
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->auth->verifyOtp($request->validated('phone'), $request->validated('otp'));

        if (! $result['success']) {
            return $this->error('Invalid or expired OTP.', 422);
        }

        $token = $result['user']->createToken('naqi-ess')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => new UserResource($result['user']),
        ], 'Account verified successfully.');
    }

    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $user = $this->auth->resendOtp($request->validated('phone'));

        abort_if(! $user, 404);

        return $this->success([], 'OTP resent successfully.');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->auth->login($request->validated('login'), $request->validated('password'));

        if (! $result['success']) {
            $message = match ($result['error_code']) {
                'deactivated' => 'Your account has been deactivated.',
                default => 'Invalid credentials.',
            };
            $code = $result['error_code'] === 'deactivated' ? 403 : 401;

            return $this->error($message, $code);
        }

        $token = $result['user']->createToken('naqi-ess')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => new UserResource($result['user']),
        ], 'Login successful.');
    }

    public function loginWithPin(LoginPinRequest $request): JsonResponse
    {
        $result = $this->auth->loginWithPin($request->validated('phone'), $request->validated('pin_code'));

        if (! $result['success']) {
            return $this->error('Invalid phone number or PIN.', 401);
        }

        $token = $result['user']->createToken('naqi-ess')->plainTextToken;

        return $this->success(['token' => $token, 'user' => new UserResource($result['user'])], 'Login successful.');
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $user = $this->auth->forgotPassword($request->validated('phone'));

        abort_if(! $user, 404);

        return $this->success([], 'OTP sent to reset your password.');
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $result = $this->auth->resetPassword(
            $request->validated('phone'),
            $request->validated('otp'),
            $request->validated('password'),
        );

        if (! $result['success']) {
            return $this->error('Invalid or expired OTP.', 422);
        }

        return $this->success([], 'Password reset successfully.');
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $result = $this->auth->changePassword(
            $request->user(),
            $request->validated('current_password'),
            $request->validated('password'),
            $request->user()->currentAccessToken()->id,
        );

        if (! $result['success']) {
            return $this->error('Current password is incorrect.', 422);
        }

        return $this->success([], 'Password changed successfully.');
    }

    public function setPin(SetPinRequest $request): JsonResponse
    {
        $result = $this->auth->setPin($request->user(), $request->validated('password'), $request->validated('pin_code'));

        if (! $result['success']) {
            return $this->error('Password is incorrect.', 422);
        }

        return $this->success([], 'PIN updated successfully.');
    }

    public function updateFcmToken(UpdateFcmTokenRequest $request): JsonResponse
    {
        $this->auth->updateFcmToken($request->user(), $request->validated('fcm_token'));

        return $this->success([], 'FCM token updated.');
    }

    public function profile(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()));
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->users->updateOwnProfile(
            $request->user(),
            $request->safe()->only(['username', 'email']),
            $request->file('image'),
        );

        return $this->success(new UserResource($user), 'Profile updated successfully.');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($request->user(), $request->user()->currentAccessToken()->id);

        return $this->success([], 'Logged out successfully.');
    }

    public function dynamicsLogin(DynamicsLoginRequest $request): JsonResponse
    {
        $result = $this->dynamicsAuth->login(
            email: $request->validated('email'),
            password: $request->validated('password'),
            deviceToken: $request->validated('device_token'),
            lang: $request->validated('lang'),
            appVersion: $request->validated('app_version'),
            devicePlatform: $request->validated('device_platform'),
        );

        if (! $result['success']) {
            return $this->dynamicsAuthErrorResponse($result, $request->validated('lang'));
        }

        $locale = $this->resolveApiLocale($request->validated('lang'));

        return $this->success(new DynamicsLoginResource($result['raw']), __('api.dynamics_otp.sent', [], $locale));
    }

    public function verifyDynamicsOtp(DynamicsVerifyOtpRequest $request): JsonResponse
    {
        $result = $this->dynamicsAuth->verifyOtp($request->validated('email'), $request->validated('otp'));

        if (! $result['success']) {
            return $this->dynamicsAuthErrorResponse($result, $request->validated('lang'));
        }

        $locale = $this->resolveApiLocale($request->validated('lang'));

        return $this->success(new DynamicsLoginResource($result['raw']), __('api.dynamics_otp.login_successful', [], $locale));
    }

    public function resendDynamicsOtp(DynamicsResendOtpRequest $request): JsonResponse
    {
        $result = $this->dynamicsAuth->resendOtp($request->validated('email'), $request->validated('lang'));

        if (! $result['success']) {
            return $this->dynamicsAuthErrorResponse($result, $request->validated('lang'));
        }

        $locale = $this->resolveApiLocale($request->validated('lang'));

        return $this->success([
            'mobile_masked' => $this->dynamicsAuth->maskMobile($result['mobile']),
        ], __('api.dynamics_otp.resent', [], $locale));
    }


    public function testSendOtp(Request $request): JsonResponse
    {
        $request->validate(['phone' => ['required', 'string']]);

        $otp = $this->auth->generateOtp();
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

    /** Maps a DynamicsAuthService failure's error_code to the right HTTP status + localized message. */
    protected function dynamicsAuthErrorResponse(array $result, ?string $lang): JsonResponse
    {
        $locale = $this->resolveApiLocale($lang);

        return match ($result['error_code']) {
            'dynamics_rejected' => $this->error($result['error'], 401),
            'no_mobile' => $this->error(__('api.dynamics_otp.no_mobile', [], $locale), 422),
            'send_failed' => $this->error(__('api.dynamics_otp.send_failed', ['error' => $result['error']], $locale), 502),
            'resend_failed' => $this->error(__('api.dynamics_otp.resend_failed', ['error' => $result['error']], $locale), 502),
            'invalid_otp' => $this->error(__('api.dynamics_otp.invalid_or_expired', [], $locale), 422),
            'session_expired' => $this->error(__('api.dynamics_otp.session_expired', [], $locale), 422),
            'no_pending_login' => $this->error(__('api.dynamics_otp.no_pending_login', [], $locale), 422),
            default => $this->error('Something went wrong.', 500),
        };
    }

    protected function resolveApiLocale(?string $lang): string
    {
        return str_starts_with(strtolower((string) $lang), 'ar') ? 'ar' : 'en';
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
