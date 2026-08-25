<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function paginate(?string $search, ?string $type, int $perPage, int $page, string $pageName): LengthAwarePaginator;

    public function findById(int $id): User;

    public function create(array $attributes): User;

    public function update(User $user, array $attributes): User;

    public function delete(User $user): void;

    /**
     * Active users matching an optional type filter and/or a specific list
     * of IDs — used to resolve a notification "audience". $type and $ids
     * are both optional; passing neither returns all active users.
     */
    public function activeUsersFor(?string $type = null, ?array $ids = null): Collection;

    public function findByPhone(string $phone): ?User;

    /** Matches phone, username, or email — whichever the "login" field actually is. */
    public function findByLoginField(string $value): ?User;
}
