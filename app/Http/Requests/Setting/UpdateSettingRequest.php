<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Key is immutable once created — deliberately not accepted here.
            'value' => ['nullable', 'string'],
            'type' => ['required', 'in:string,integer,boolean,json'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_public' => ['sometimes', 'boolean'],
        ];
    }

    public function payload(): array
    {
        $data = $this->validated();
        $data['is_public'] = $this->boolean('is_public');

        return $data;
    }
}
