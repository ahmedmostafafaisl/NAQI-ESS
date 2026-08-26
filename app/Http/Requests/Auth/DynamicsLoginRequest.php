<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class DynamicsLoginRequest extends FormRequest
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
            'device_id' => ['nullable', 'string'],
            'lang' => ['nullable', 'string', 'in:en-us,ar-sa,en,ar'],
            'app_version' => ['nullable', 'string'],
            'device_platform' => ['nullable', 'string', 'in:android,ios'],
            'resource' => ['nullable', 'url'],
        ];
    }
}
