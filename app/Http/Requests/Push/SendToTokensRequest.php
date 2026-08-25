<?php

namespace App\Http\Requests\Push;

use Illuminate\Foundation\Http\FormRequest;

class SendToTokensRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization handled by route middleware (permission:notifications.send)
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'tokens' => ['required', 'array', 'min:1', 'max:1000'],
            'tokens.*' => ['string'],
            'data' => ['sometimes', 'array'],
        ];
    }
}
