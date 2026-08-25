<?php

namespace App\Http\Requests\Dynamics;

class AttendanceRecordRequest extends BaseDynamicsRequest
{
    protected function additionalRules(): array
    {
        return [
            'punch_date' => ['required', 'date_format:Y-m-d'],
            'team_worker_personnel_number' => ['nullable', 'string'],
        ];
    }
}
