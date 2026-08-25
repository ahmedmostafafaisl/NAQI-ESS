<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

interface RoleRepositoryInterface
{
    public function paginate(int $perPage, int $page, string $pageName): LengthAwarePaginator;

    public function all(): Collection;

    public function findById(int $id): Role;

    public function create(string $name, array $permissionNames): Role;

    public function update(Role $role, string $name, array $permissionNames): Role;

    public function delete(Role $role): void;
}
