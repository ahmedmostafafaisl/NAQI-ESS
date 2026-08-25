<?php

namespace App\Http\Requests\Dynamics;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Deliberately does NOT extend BaseDynamicsRequest — this endpoint logs in
 * with email/password (like auth/dynamics-login), it doesn't take an
 * existing session token the way the other Dynamics endpoints do.
 */
class HomePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_token' => ['nullable', 'string'],
            'lang' => ['nullable', 'string', 'in:en-us,ar-sa'],
        ];
    }
}
