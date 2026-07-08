<?php

return [
    // Azure AD app registration (client credentials flow)
    'tenant_id' => env('DYNAMICS_TENANT_ID'),
    'client_id' => env('DYNAMICS_CLIENT_ID'),
    'client_secret' => env('DYNAMICS_CLIENT_SECRET'),

    // Dynamics 365 environment (e.g. https://yourorg.crm.dynamics.com)
    'resource' => env('DYNAMICS_RESOURCE'),

    // Web API version, e.g. v9.2
    'api_version' => env('DYNAMICS_API_VERSION', 'v9.2'),

    // Cache key used to store the access token between requests
    'token_cache_key' => env('DYNAMICS_TOKEN_CACHE_KEY', 'dynamics365_access_token'),

    // Entity set names in Dynamics 365 - adjust to match your org's schema
    'entities' => [
        'employees' => 'contacts',
        'leave_requests' => 'naqi_leaverequests',
        'attendance' => 'naqi_attendances',
        'payslips' => 'naqi_payslips',
    ],

    // HTTP client behaviour
    'timeout' => 30,
    'retry_times' => 2,
    'retry_sleep_ms' => 300,
];
