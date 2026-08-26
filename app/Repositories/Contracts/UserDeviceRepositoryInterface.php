<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use App\Models\UserDevice;

interface UserDeviceRepositoryInterface
{
    public function upsert(User $user, string $deviceId, string $fcmToken): UserDevice;
    public function tokensExcept(User $user, ?string $exceptDeviceId = null): array;
    public function recentTokensExcept(User $user, ?string $exceptDeviceId, int $limit): array;
}
