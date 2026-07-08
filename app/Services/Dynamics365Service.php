<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Generic client + domain helpers for talking to Microsoft Dynamics 365
 * (Web API, OAuth2 client-credentials flow via Azure AD).
 */
class Dynamics365Service
{
    protected string $resource;
    protected string $apiVersion;

    public function __construct()
    {
        $this->resource = rtrim(config('dynamics365.resource'), '/');
        $this->apiVersion = config('dynamics365.api_version');
    }

    /**
     * Fetch (and cache) an OAuth2 access token using the client credentials grant.
     */
    public function getAccessToken(): string
    {
        return Cache::remember(config('dynamics365.token_cache_key'), 3300, function () {
            $tenantId = config('dynamics365.tenant_id');

            $response = Http::asForm()
                ->timeout(config('dynamics365.timeout'))
                ->post("https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token", [
                    'grant_type' => 'client_credentials',
                    'client_id' => config('dynamics365.client_id'),
                    'client_secret' => config('dynamics365.client_secret'),
                    'scope' => $this->resource.'/.default',
                ]);

            if ($response->failed()) {
                Log::error('Dynamics365: failed to obtain access token', ['body' => $response->body()]);
                throw new RuntimeException('Unable to authenticate with Dynamics 365: '.$response->body());
            }

            return $response->json('access_token');
        });
    }

    /**
     * Base authenticated HTTP client pointed at the Web API root.
     */
    protected function client()
    {
        return Http::withToken($this->getAccessToken())
            ->baseUrl("{$this->resource}/api/data/{$this->apiVersion}")
            ->timeout(config('dynamics365.timeout'))
            ->retry(config('dynamics365.retry_times'), config('dynamics365.retry_sleep_ms'))
            ->withHeaders([
                'Accept' => 'application/json',
                'OData-MaxVersion' => '4.0',
                'OData-Version' => '4.0',
                'Content-Type' => 'application/json; charset=utf-8',
                'Prefer' => 'return=representation',
            ]);
    }

    /** Retrieve a collection with optional OData query params ($filter, $select, $expand...) */
    public function get(string $entitySet, array $query = []): array
    {
        $response = $this->client()->get($entitySet, $query);
        $this->throwIfFailed($response, "GET {$entitySet}");

        return $response->json();
    }

    /** Retrieve a single record by its GUID */
    public function find(string $entitySet, string $id, array $select = []): array
    {
        $query = $select ? ['$select' => implode(',', $select)] : [];
        $response = $this->client()->get("{$entitySet}({$id})", $query);
        $this->throwIfFailed($response, "GET {$entitySet}({$id})");

        return $response->json();
    }

    /** Create a record, returns the new record's GUID */
    public function create(string $entitySet, array $payload): string
    {
        $response = $this->client()->post($entitySet, $payload);
        $this->throwIfFailed($response, "POST {$entitySet}");

        $entityUri = $response->header('OData-EntityId') ?? '';
        preg_match('/\(([0-9a-fA-F-]{36})\)/', $entityUri, $matches);

        return $matches[1] ?? $response->json('id') ?? '';
    }

    /** Update an existing record (partial patch) */
    public function update(string $entitySet, string $id, array $payload): void
    {
        $response = $this->client()->patch("{$entitySet}({$id})", $payload);
        $this->throwIfFailed($response, "PATCH {$entitySet}({$id})");
    }

    /** Delete a record */
    public function delete(string $entitySet, string $id): void
    {
        $response = $this->client()->delete("{$entitySet}({$id})");
        $this->throwIfFailed($response, "DELETE {$entitySet}({$id})");
    }

    protected function throwIfFailed($response, string $context): void
    {
        if ($response->failed()) {
            Log::error("Dynamics365: {$context} failed", ['body' => $response->body()]);
            throw new RuntimeException("Dynamics 365 request failed ({$context}): ".$response->body());
        }
    }

    /*
    |--------------------------------------------------------------------
    | Naqi ESS domain helpers
    |--------------------------------------------------------------------
    | High level, ESS-specific wrappers built on top of the generic
    | client above. Adjust entity/field names to match your org's schema.
    */

    /** Push a local employee record into Dynamics as a contact and store the GUID */
    public function syncEmployee(User $user): string
    {
        $entitySet = config('dynamics365.entities.employees');

        $payload = [
            'fullname' => $user->username,
            'emailaddress1' => $user->email,
            'mobilephone' => $user->phone,
            'naqi_personnelnumber' => $user->personnel_number,
        ];

        if ($user->dynamics_id) {
            $this->update($entitySet, $user->dynamics_id, $payload);
            $id = $user->dynamics_id;
        } else {
            $id = $this->create($entitySet, $payload);
        }

        $user->update(['dynamics_id' => $id, 'dynamics_synced_at' => now()]);

        return $id;
    }

    /** Pull an employee's profile from Dynamics */
    public function getEmployee(string $dynamicsId): array
    {
        return $this->find(config('dynamics365.entities.employees'), $dynamicsId);
    }

    /** Submit a leave request to Dynamics on behalf of a user */
    public function submitLeaveRequest(User $user, array $data): string
    {
        $entitySet = config('dynamics365.entities.leave_requests');

        return $this->create($entitySet, array_merge($data, [
            'naqi_employeeid@odata.bind' => "/".config('dynamics365.entities.employees')."({$user->dynamics_id})",
        ]));
    }

    /** Fetch attendance logs for an employee within a date range */
    public function getAttendance(User $user, string $from, string $to): array
    {
        $entitySet = config('dynamics365.entities.attendance');

        return $this->get($entitySet, [
            '$filter' => "_naqi_employeeid_value eq {$user->dynamics_id} and naqi_date ge {$from} and naqi_date le {$to}",
        ]);
    }

    /** Fetch payslips for an employee */
    public function getPayslips(User $user): array
    {
        $entitySet = config('dynamics365.entities.payslips');

        return $this->get($entitySet, [
            '$filter' => "_naqi_employeeid_value eq {$user->dynamics_id}",
            '$orderby' => 'naqi_period desc',
        ]);
    }
}
