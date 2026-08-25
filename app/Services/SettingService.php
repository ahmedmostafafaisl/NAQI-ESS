<?php

namespace App\Services;

use App\Models\Setting;
use App\Repositories\Contracts\SettingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class SettingService
{
    public function __construct(protected SettingRepositoryInterface $settings) {}

    public function paginate(?string $search, int $perPage, int $page, string $pageName): LengthAwarePaginator
    {
        return $this->settings->paginate($search, $perPage, $page, $pageName);
    }

    public function publicSettings(): Collection
    {
        return $this->settings->allPublic();
    }

    public function find(string $key): Setting
    {
        $setting = $this->settings->findByKey($key);

        abort_if(! $setting, 404);

        return $setting;
    }

    public function create(array $data): Setting
    {
        $this->assertValidJsonIfNeeded($data);

        return $this->settings->create($data);
    }

    public function update(string $key, array $data): Setting
    {
        $this->assertValidJsonIfNeeded($data);

        return $this->settings->updateByKey($key, $data);
    }

    public function delete(string $key): void
    {
        $this->settings->deleteByKey($key);
    }

    /**
     * Cross-field business rule that can't live in a simple validation
     * string rule: if type is "json", value must actually be valid JSON.
     */
    protected function assertValidJsonIfNeeded(array $data): void
    {
        if (($data['type'] ?? null) !== 'json' || empty($data['value'])) {
            return;
        }

        json_decode($data['value']);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ValidationException::withMessages(['value' => 'The value must be valid JSON.']);
        }
    }
}
