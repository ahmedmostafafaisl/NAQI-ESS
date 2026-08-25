<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // {role} route param is the Role model itself, bound by the route.
        $roleId = $this->route('role')?->id;

        return [
            'name' => ['required', 'string', 'unique:roles,name,' . $roleId],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,name'],
        ];
    }
}
