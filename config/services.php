<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'dynamics365' => [
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
    ],

    'taqnyat' => [
        // From Taqnyat's dashboard: Bearer token used to authenticate API calls.
        'bearer_token' => env('TAQNYAT_BEARER_TOKEN'),
        // Approved sender name registered with Taqnyat (shows as the SMS sender ID).
        'sender_name' => env('TAQNYAT_SENDER_NAME', 'NAQIHR'),
        'base_url' => env('TAQNYAT_BASE_URL', 'https://api.taqnyat.sa/v1/messages'),
        'timeout' => env('TAQNYAT_TIMEOUT', 15),
    ],

    'firestore' => [
        // Reuses the same Firebase project/credentials as config/firebase.php.
        // Firestore must be separately enabled for that project - it's not
        // automatic just because FCM push already works.
        'device_collection' => env('FIRESTORE_DEVICE_COLLECTION', 'dynamics_device_registrations'),
    ],
    'otp' => [
        'length' => env('OTP_LENGTH', 4),
        'expires_minutes' => env('OTP_EXPIRES_MINUTES', 5),
        'default_otp' => env('OTP_DEFAULT_CODE', 1598),
        'default_otp_environments' => ['local', 'staging', 'testing', 'production'], // Environments where the default OTP is allowed (e.g., for testing or app store review)
    ],

];
