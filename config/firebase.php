<?php

return [

    'default' => env('FIREBASE_PROJECT', 'app'),

    'projects' => [
        'app' => [
            /*
             * Path to the Firebase service account JSON file, downloaded from:
             * Firebase Console -> Project Settings -> Service Accounts -> Generate new private key.
             *
             * Place the downloaded file at the path below (already git-ignored).
             */
            'credentials' => [
                'file' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase/firebase_credentials.json')),
                'auto_discovery' => true,
            ],

            'database' => [
                'url' => env('FIREBASE_DATABASE_URL'),
            ],

            'dynamic_links' => [
                'default_domain' => env('FIREBASE_DYNAMIC_LINKS_DEFAULT_DOMAIN'),
            ],

            'storage' => [
                'default_bucket' => env('FIREBASE_STORAGE_DEFAULT_BUCKET'),
            ],

            'logging' => [
                'http_log_channel' => env('FIREBASE_HTTP_LOG_CHANNEL'),
                'http_debug_log_channel' => env('FIREBASE_HTTP_DEBUG_LOG_CHANNEL'),
            ],

            'cache_store' => env('FIREBASE_CACHE_STORE', 'file'),
        ],
    ],
];
