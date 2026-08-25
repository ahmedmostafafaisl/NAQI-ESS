<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'category' => $this->resource['category'],
            'request_id' => $this->resource['request_id'],
            'request_type' => $this->resource['request_type'],
            'status' => $this->resource['status'],
            'creation_date' => $this->resource['creation_date'],
            'period' => $this->resource['period'],
            'details' => $this->resource['details'],
        ];
    }
}
