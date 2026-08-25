<?php

return [
    'length' => env('OTP_LENGTH', 4),
    'expires_minutes' => env('OTP_EXPIRES_MINUTES', 5),
    'default_otp' => env('OTP_DEFAULT_CODE', 1598),
    'default_otp_environments' => ['local', 'staging', 'testing', 'production'], // Environments where the default OTP is allowed (e.g., for testing or app store review)
];
