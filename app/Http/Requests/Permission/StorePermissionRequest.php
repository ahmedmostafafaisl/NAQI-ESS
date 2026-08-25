<?php

namespace App\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization handled by route middleware (permission:permissions.manage)
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'unique:permissions,name'],
        ];
    }
}
