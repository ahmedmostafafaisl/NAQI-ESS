<?php

return [
    'length' => env('OTP_LENGTH', 4),
    'expires_minutes' => env('OTP_EXPIRES_MINUTES', 5),
    'default_otp' => env('OTP_DEFAULT_CODE'),
    'default_otp_environments' => ['local', 'staging', 'testing'],
];
