<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $users,
        protected RoleRepositoryInterface $roles,
    ) {}

    public function paginate(?string $search, ?string $type, int $perPage, int $page, string $pageName): LengthAwarePaginator
    {
        return $this->users->paginate($search, $type, $perPage, $page, $pageName);
    }

    public function find(int $id): User
    {
        return $this->users->findById($id);
    }

    /** name => name map, for the role <select> on the create/edit forms. */
    public function assignableRoles(): Collection
    {
        return $this->roles->all()->pluck('name', 'name');
    }

    /**
     * $data is expected to include a 'role' key (the role to assign) on top
     * of the user's own fillable attributes — 'role' is never itself a
     * persisted column (Spatie's role tables are the single source of
     * truth for that), so it's split out here rather than left for the
     * repository to accidentally mass-assign.
     */
    public function create(array $data): User
    {
        $role = $data['role'];
        unset($data['role']);

        $data['password'] = Hash::make($data['password']);

        $user = $this->users->create($data);
        $user->assignRole($role);

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $role = $data['role'];
        unset($data['role']);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']); // don't overwrite with an empty value
        }

        $user = $this->users->update($user, $data);
        $user->syncRoles([$role]);

        return $user;
    }

    public function delete(User $user): void
    {
        $this->users->delete($user);
    }

    /**
     * Self-service profile update — deliberately separate from update()
     * above, which requires a 'role' key and is meant for admin-managed
     * user editing. A user updating their own profile must never be able
     * to assign themselves a role, so this bypasses that logic entirely
     * rather than requiring callers to pass a role and then ignoring it.
     */
    public function updateOwnProfile(User $user, array $data, ?\Illuminate\Http\UploadedFile $image = null): User
    {
        if ($image) {
            $data['image'] = $image->store('users', 'public');
        }

        return $this->users->update($user, $data);
    }
}
