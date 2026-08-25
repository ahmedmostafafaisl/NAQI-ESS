<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization handled by route middleware (permission:users.create)
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6'],
            'type' => ['required', 'in:employee,customer'],
            'status' => ['required', 'in:active,inactive'],
            'role' => ['required', 'exists:roles,name'],
        ];
    }
}
