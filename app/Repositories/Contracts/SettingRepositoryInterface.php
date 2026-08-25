<?php

namespace App\Repositories\Contracts;

use App\Models\Setting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Data-access boundary for Setting. Controllers and Services never query
 * the Setting model directly — everything goes through here, so the
 * persistence layer (Eloquent today) can change without touching business
 * logic or HTTP concerns.
 */
interface SettingRepositoryInterface
{
    public function paginate(?string $search, int $perPage, int $page, string $pageName): LengthAwarePaginator;

    public function allPublic(): Collection;

    public function findByKey(string $key): ?Setting;

    public function keyExists(string $key): bool;

    public function create(array $attributes): Setting;

    public function updateByKey(string $key, array $attributes): Setting;

    public function deleteByKey(string $key): void;
}
