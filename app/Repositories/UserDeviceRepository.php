<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\UserDevice;
use App\Repositories\Contracts\UserDeviceRepositoryInterface;

class UserDeviceRepository implements UserDeviceRepositoryInterface
{
    public function upsert(User $user, string $deviceId, string $fcmToken): UserDevice
    {
        return UserDevice::updateOrCreate(
            ['user_id' => $user->id, 'device_id' => $deviceId],
            ['fcm_token' => $fcmToken],
        );
    }

    public function tokensExcept(User $user, ?string $exceptDeviceId = null): array
    {
        return $user->devices()
            ->when($exceptDeviceId, fn($q) => $q->where('device_id', '!=', $exceptDeviceId))
            ->pluck('fcm_token')
            ->all();
    }

    public function recentTokensExcept(User $user, ?string $exceptDeviceId, int $limit): array
    {
        return $user->devices()
            ->when($exceptDeviceId, fn($q) => $q->where('device_id', '!=', $exceptDeviceId))
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->pluck('fcm_token')
            ->all();
    }
}
