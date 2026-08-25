<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'attendance_status' => $this->resource['attendance_status'],
            'time_in' => $this->resource['time_in'],
            'time_out' => $this->resource['time_out'],
            'profile_time_in' => $this->resource['profile_time_in'],
            'profile_time_out' => $this->resource['profile_time_out'],
            'worked_hours' => $this->resource['worked_hours'],
            'difference' => $this->resource['difference'],
            'punch_latitude' => $this->resource['punch_latitude'],
            'punch_longitude' => $this->resource['punch_longitude'],
        ];
    }
}
