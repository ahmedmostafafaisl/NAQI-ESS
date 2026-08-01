<?php

return [
    // Azure AD app registration (client credentials flow)
    'tenant_id' => env('DYNAMICS_TENANT_ID'),
    'client_id' => env('DYNAMICS_CLIENT_ID'),
    'client_secret' => env('DYNAMICS_CLIENT_SECRET'),

    // Dynamics 365 environment (e.g. https://yourorg.axcloud.dynamics.com),
    // also sent as the `resource` param when requesting a token.
    'resource' => env('DYNAMICS_RESOURCE'),

    /*
    |----------------------------------------------------------------
    | Dataverse / CRM Web API (OData) — used by get()/find()/create()/
    | update()/delete() below. Only relevant if this org also exposes
    | the standard Dataverse Web API at /api/data/{version}/...
    |----------------------------------------------------------------
    */
    'api_version' => env('DYNAMICS_API_VERSION', 'v9.2'),

    'entities' => [
        'employees' => 'contacts',
        'leave_requests' => 'naqi_leaverequests',
        'attendance' => 'naqi_attendances',
        'payslips' => 'naqi_payslips',
    ],

    /*
    |----------------------------------------------------------------
    | Finance & Operations custom X++ services (used by callService()
    | and loginUser() below) — called at /api/services/{group}/{service}/
    | {operation}, a completely different calling convention from the
    | Dataverse OData API above. This is the actual integration style
    | confirmed working for this Naqi ESS <-> D365 F&O environment.
    |----------------------------------------------------------------
    */
    'service_group' => env('DYNAMICS_SERVICE_GROUP', 'INDXNaqiEssServiceGroup'),

    // Default language sent as `lang` in F&O service contracts (D365 F&O
    // expects locale codes like en-us / ar-sa, not en / ar).
    'default_lang' => env('DYNAMICS_DEFAULT_LANG', 'en-us'),

    // Cache key used to store the app-level (client credentials) access token
    'token_cache_key' => env('DYNAMICS_TOKEN_CACHE_KEY', 'dynamics365_access_token'),

    // Safety margin (seconds) subtracted from the token's real expires_in
    // before caching, so we never hand out a token that's about to expire
    // mid-request.
    'token_expiry_buffer' => env('DYNAMICS_TOKEN_EXPIRY_BUFFER', 60),

    // HTTP client behaviour
    'timeout' => 30,
    'retry_times' => 2,
    'retry_sleep_ms' => 300,
];
