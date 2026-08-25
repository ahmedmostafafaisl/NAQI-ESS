<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMembersResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'is_manager' => $this->resource['is_manager'],
            'team' => $this->resource['team'],
            'managers' => $this->resource['managers'],
        ];
    }
}
