<?php

namespace App\Repositories\Contracts;

use App\Models\DynamicsUser;


interface DynamicsUserRepositoryInterface
{
    public function findByEmail(string $email): ?DynamicsUser;

    public function updateOrCreate(string $email, array $attributes): DynamicsUser;
}
