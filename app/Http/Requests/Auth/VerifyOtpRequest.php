<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string'],
            'otp' => ['required', 'string'],
            'device_id' => ['nullable', 'string', 'max:255', 'required_with:fcm_token'],
            'fcm_token' => ['nullable', 'string', 'required_with:device_id'],
        ];
    }
}
