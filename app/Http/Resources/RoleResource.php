<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'permissions_count' => $this->when(isset($this->permissions_count), $this->permissions_count),
            'permissions' => $this->whenLoaded('permissions', fn() => $this->permissions->pluck('name')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
