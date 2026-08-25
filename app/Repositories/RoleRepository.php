<?php

namespace App\Repositories;

use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class RoleRepository implements RoleRepositoryInterface
{
    public function paginate(int $perPage, int $page, string $pageName): LengthAwarePaginator
    {
        return Role::withCount('permissions')->paginate(perPage: $perPage, page: $page, pageName: $pageName);
    }

    public function all(): Collection
    {
        return Role::all();
    }

    public function findById(int $id): Role
    {
        return Role::findOrFail($id);
    }

    public function create(string $name, array $permissionNames): Role
    {
        $role = Role::create(['name' => $name, 'guard_name' => 'web']);
        $role->syncPermissions($permissionNames);

        return $role;
    }

    public function update(Role $role, string $name, array $permissionNames): Role
    {
        $role->update(['name' => $name]);
        $role->syncPermissions($permissionNames);

        return $role->fresh();
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }
}
