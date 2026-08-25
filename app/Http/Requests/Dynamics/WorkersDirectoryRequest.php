<?php

namespace App\Http\Requests\Dynamics;

class WorkersDirectoryRequest extends BaseDynamicsRequest
{
    protected function additionalRules(): array
    {
        return [
            'letter' => ['required', 'string', 'max:1'],
        ];
    }
}
