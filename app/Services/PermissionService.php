<?php

namespace App\Services;

use App\Repositories\Contracts\PermissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    public function __construct(protected PermissionRepositoryInterface $permissions) {}

    public function paginate(int $perPage, int $page, string $pageName): LengthAwarePaginator
    {
        return $this->permissions->paginate($perPage, $page, $pageName);
    }

    public function groupedByPrefix(): Collection
    {
        return $this->permissions->allGroupedByPrefix();
    }

    public function create(string $name): Permission
    {
        return $this->permissions->create($name);
    }

    public function delete(Permission $permission): void
    {
        $this->permissions->delete($permission);
    }
}
