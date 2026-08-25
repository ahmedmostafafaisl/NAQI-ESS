<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

interface PermissionRepositoryInterface
{
    public function paginate(int $perPage, int $page, string $pageName): LengthAwarePaginator;

    public function all(): Collection;

    /** Used by RoleService::permissionsGroupedByPrefix() — see that class for why. */
    public function allGroupedByPrefix(): Collection;

    public function findById(int $id): Permission;

    public function create(string $name): Permission;

    public function delete(Permission $permission): void;
}
