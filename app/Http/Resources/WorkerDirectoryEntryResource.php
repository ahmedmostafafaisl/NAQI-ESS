<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkerDirectoryEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->resource['name'],
            'position' => $this->resource['position'],
            'phone' => $this->resource['phone'],
            'email' => $this->resource['email'],
        ];
    }
}
