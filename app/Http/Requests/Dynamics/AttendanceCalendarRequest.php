<?php

namespace App\Http\Requests\Dynamics;

class AttendanceCalendarRequest extends BaseDynamicsRequest
{
    protected function additionalRules(): array
    {
        return [
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'digits:4'],
            'team_worker_personnel_number' => ['nullable', 'string'],
        ];
    }
}
