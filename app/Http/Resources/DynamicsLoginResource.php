<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class DynamicsLoginResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $body = $this->resource;
        $data = $body['Data'] ?? [];

        $mobile = $data['Mobile'] ?? null;
        // unset($data['Mobile']);
        $data = ['mobile_masked' => $mobile ? $this->maskMobile($mobile) : null] + $data;

        return [
            'MobileVersionErr' => $body['MobileVersionErr'] ?? false,
            'Status' => $body['Status'] ?? false,
            'Data' => $data,
            'Error' => $body['Error'] ?? null,
            'Code' => $body['Code'] ?? null,
            'UpdateUserServicesAccessList' => $body['UpdateUserServicesAccessList'] ?? false,
            'IsManager' => $body['IsManager'] ?? false,
            'UserServicesAccessList' => $body['UserServicesAccessList'] ?? null,
        ];
    }

    protected function maskMobile(string $mobile): string
    {
        $digits = preg_replace('/\D/', '', $mobile);
        $tail = substr($digits, -2);

        return str_repeat('*', max(0, strlen($digits) - 2)) . $tail;
    }
}
