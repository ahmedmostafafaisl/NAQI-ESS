<?php

namespace App\Http\Requests\Dynamics;

use Illuminate\Foundation\Http\FormRequest;


abstract class BaseDynamicsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // these are pass-through endpoints; identity is proven by the Dynamics token itself, not Laravel auth
    }

    public function rules(): array
    {
        return array_merge([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'lang' => ['nullable', 'string', 'in:en-us,ar'],
        ], $this->additionalRules());
    }

    /** Override in each subclass to add endpoint-specific fields. */
    protected function additionalRules(): array
    {
        return [];
    }
}
