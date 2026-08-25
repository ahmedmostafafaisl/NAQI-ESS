<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization handled by route middleware / @can in the view
    }

    public function rules(): array
    {
        return [
            'send_mode' => ['required', 'in:audience,tokens'],

            // send_mode = audience
            'audience' => ['required_if:send_mode,audience', 'in:all,employees,customers,specific'],
            'user_ids' => ['required_if:audience,specific', 'array'],

            // send_mode = tokens
            'tokens' => ['required_if:send_mode,tokens', 'string'],

            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
        ];
    }

    /** Raw textarea input split into a clean array of tokens (newline/comma separated, blanks trimmed). */
    public function parsedTokens(): array
    {
        return collect(preg_split('/[\r\n,]+/', (string) $this->input('tokens')))
            ->map(fn($t) => trim($t))
            ->filter()
            ->values()
            ->all();
    }
}
