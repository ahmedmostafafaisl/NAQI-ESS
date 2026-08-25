<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // {user} route param is the User model itself, bound by the route.
        $userId = $this->route('user')?->id;

        return [
            'username' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email,' . $userId],
            'phone' => ['required', 'string', 'unique:users,phone,' . $userId],
            'password' => ['nullable', 'string', 'min:6'],
            'type' => ['required', 'in:employee,customer'],
            'status' => ['required', 'in:active,inactive'],
            'role' => ['required', 'exists:roles,name'],
        ];
    }
}
