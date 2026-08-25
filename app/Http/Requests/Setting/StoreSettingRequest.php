<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization handled by route middleware (permission:settings.manage)
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:255', 'unique:settings,key', 'regex:/^[a-z0-9_.]+$/'],
            'value' => ['nullable', 'string'],
            'type' => ['required', 'in:string,integer,boolean,json'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_public' => ['sometimes', 'boolean'],
        ];
    }

    /** Normalized data ready to hand to SettingService::create(). */
    public function payload(): array
    {
        $data = $this->validated();
        $data['is_public'] = $this->boolean('is_public');

        return $data;
    }
}
