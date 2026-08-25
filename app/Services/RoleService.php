<?php

namespace App\Services;

use App\Repositories\Contracts\PermissionRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class RoleService
{
    /** Roles that can never be deleted, regardless of who's asking. */
    protected const PROTECTED_ROLES = ['super-admin', 'admin'];

    public function __construct(
        protected RoleRepositoryInterface $roles,
        protected PermissionRepositoryInterface $permissions,
    ) {}

    public function paginate(int $perPage, int $page, string $pageName): LengthAwarePaginator
    {
        return $this->roles->paginate($perPage, $page, $pageName);
    }

    public function find(int $id): Role
    {
        return $this->roles->findById($id);
    }

    /** All permissions grouped by their prefix (e.g. "users.view" -> group "users") — used to render the permission checkboxes. */
    public function permissionsGroupedByPrefix(): Collection
    {
        return $this->permissions->allGroupedByPrefix();
    }

    public function create(string $name, array $permissionNames): Role
    {
        return $this->roles->create($name, $permissionNames);
    }

    public function update(Role $role, string $name, array $permissionNames): Role
    {
        return $this->roles->update($role, $name, $permissionNames);
    }

    /** @throws ValidationException if the role is protected */
    public function delete(Role $role): void
    {
        if ($this->isProtected($role)) {
            throw ValidationException::withMessages(['role' => 'This role cannot be deleted.']);
        }

        $this->roles->delete($role);
    }

    public function isProtected(Role $role): bool
    {
        return in_array($role->name, self::PROTECTED_ROLES, true);
    }
}
