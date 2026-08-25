<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function paginate(?string $search, ?string $type, int $perPage, int $page, string $pageName): LengthAwarePaginator
    {
        return User::query()
            ->when($search, fn($q) => $q->where('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%"))
            ->when($type, fn($q) => $q->where('type', $type))
            ->latest()
            ->paginate(perPage: $perPage, page: $page, pageName: $pageName);
    }

    public function findById(int $id): User
    {
        return User::findOrFail($id);
    }

    public function create(array $attributes): User
    {
        return User::create($attributes);
    }

    public function update(User $user, array $attributes): User
    {
        $user->fill($attributes);
        $user->save();

        return $user->fresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function activeUsersFor(?string $type = null, ?array $ids = null): Collection
    {
        return User::query()
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($ids !== null, fn($q) => $q->whereIn('id', $ids))
            ->active()
            ->orderBy('username')
            ->get();
    }

    public function findByPhone(string $phone): ?User
    {
        return User::where('phone', $phone)->first();
    }

    public function findByLoginField(string $value): ?User
    {
        return User::where('phone', $value)
            ->orWhere('username', $value)
            ->orWhere('email', $value)
            ->first();
    }
}
