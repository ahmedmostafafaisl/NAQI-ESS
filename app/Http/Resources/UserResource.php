<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'type' => $this->type,
            'status' => $this->status,
            'personnel_number' => $this->personnel_number,
            'image_url' => $this->image_url,
            'role' => $this->getRoleNames()->first(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
