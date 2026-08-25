<?php

namespace App\Http\Requests\Push;

use Illuminate\Foundation\Http\FormRequest;

class SendToAudienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization handled by route middleware (permission:notifications.send)
    }

    public function rules(): array
    {
        return [
            'audience' => ['required', 'in:all,employees,customers,specific'],
            'user_ids' => ['required_if:audience,specific', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'data' => ['sometimes', 'array'],
        ];
    }
}
