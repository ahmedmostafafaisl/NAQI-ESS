<?php

namespace App\Repositories;

use App\Repositories\Contracts\PermissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class PermissionRepository implements PermissionRepositoryInterface
{
    public function paginate(int $perPage, int $page, string $pageName): LengthAwarePaginator
    {
        return Permission::latest()->paginate(perPage: $perPage, page: $page, pageName: $pageName);
    }

    public function all(): Collection
    {
        return Permission::all();
    }

    public function allGroupedByPrefix(): Collection
    {
        return Permission::all()->groupBy(fn(Permission $p) => explode('.', $p->name)[0]);
    }

    public function findById(int $id): Permission
    {
        return Permission::findOrFail($id);
    }

    public function create(string $name): Permission
    {
        return Permission::create(['name' => $name, 'guard_name' => 'web']);
    }

    public function delete(Permission $permission): void
    {
        $permission->delete();
    }
}
