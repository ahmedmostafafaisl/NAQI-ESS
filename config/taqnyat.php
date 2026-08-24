<?php

return [
    // From Taqnyat's dashboard: Bearer token used to authenticate API calls.
    'bearer_token' => env('TAQNYAT_BEARER_TOKEN'),
    // Approved sender name registered with Taqnyat (shows as the SMS sender ID).
    'sender_name' => env('TAQNYAT_SENDER_NAME', 'NaqiESS'),
    'base_url' => env('TAQNYAT_BASE_URL', 'https://api.taqnyat.sa/v1/messages'),
    'timeout' => env('TAQNYAT_TIMEOUT', 15),
];
