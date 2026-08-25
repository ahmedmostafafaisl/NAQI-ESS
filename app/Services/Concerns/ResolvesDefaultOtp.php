<?php

namespace App\Services\Concerns;

trait ResolvesDefaultOtp
{

    protected function matchesDefaultOtp(string $submittedOtp): bool
    {
        $defaultOtp = config('otp.default_otp');
        $allowedEnvironments = config('otp.default_otp_environments', []);

        if (empty($defaultOtp) || ! app()->environment($allowedEnvironments)) {
            return false;
        }

        return hash_equals((string) $defaultOtp, $submittedOtp);
    }
}
