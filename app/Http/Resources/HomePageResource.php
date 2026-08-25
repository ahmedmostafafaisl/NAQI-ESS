<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomePageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'token' => $this->resource['token'],
            'worker' => $this->resource['worker'],
            'is_manager' => $this->resource['is_manager'],
            'name' => $this->resource['name'],
            'gender' => $this->resource['gender'],
            'shift_start_time' => $this->resource['shift_start_time'],
            'shift_end_time' => $this->resource['shift_end_time'],
            'can_clock_in' => $this->resource['can_clock_in'],
            'can_clock_out' => $this->resource['can_clock_out'],
            'clock_in_time' => $this->resource['clock_in_time'],
            'clock_out_time' => $this->resource['clock_out_time'],
            'worker_tasks_counter' => $this->resource['worker_tasks_counter'],
            'team_approval_counter' => $this->resource['team_approval_counter'],
            'sick_leave' => $this->resource['sick_leave'],
            'annual_leave' => $this->resource['annual_leave'],
            'worker_off_today' => $this->resource['worker_off_today'],
            'worker_remotely_today' => $this->resource['worker_remotely_today'],
            'company_announcements' => $this->resource['company_announcements'],
            'company_upcoming_events' => $this->resource['company_upcoming_events'],
        ];
    }
}
