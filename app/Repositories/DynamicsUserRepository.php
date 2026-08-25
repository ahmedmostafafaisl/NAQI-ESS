<?php

namespace App\Repositories;

use App\Models\DynamicsUser;
use App\Repositories\Contracts\DynamicsUserRepositoryInterface;

class DynamicsUserRepository implements DynamicsUserRepositoryInterface
{
    public function findByEmail(string $email): ?DynamicsUser
    {
        return DynamicsUser::where('email', $email)->first();
    }

    public function updateOrCreate(string $email, array $attributes): DynamicsUser
    {
        return DynamicsUser::updateOrCreate(['email' => $email], $attributes);
    }
}
