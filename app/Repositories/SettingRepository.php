<?php

namespace App\Repositories;

use App\Models\Setting;
use App\Repositories\Contracts\SettingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SettingRepository implements SettingRepositoryInterface
{
    public function paginate(?string $search, int $perPage, int $page, string $pageName): LengthAwarePaginator
    {
        return Setting::query()
            ->when($search, fn($q) => $q->where('key', 'like', "%{$search}%"))
            ->orderBy('key')
            ->paginate(perPage: $perPage, page: $page, pageName: $pageName);
    }

    public function allPublic(): Collection
    {
        return Setting::where('is_public', true)->get();
    }

    public function findByKey(string $key): ?Setting
    {
        return Setting::where('key', $key)->first();
    }

    public function keyExists(string $key): bool
    {
        return Setting::where('key', $key)->exists();
    }

    public function create(array $attributes): Setting
    {
        return Setting::create($attributes);
    }

    public function updateByKey(string $key, array $attributes): Setting
    {
        $setting = Setting::where('key', $key)->firstOrFail();
        $setting->update($attributes);

        return $setting->fresh();
    }

    public function deleteByKey(string $key): void
    {
        Setting::where('key', $key)->firstOrFail()->delete();
    }
}
