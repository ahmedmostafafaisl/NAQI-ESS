<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Data-access boundary for a notifiable model's own in-app notification
 * feed (Laravel's native `notifications` table) — reading, marking read,
 * deleting. Sending notifications is a separate concern, handled by
 * NotificationService directly (it orchestrates the DB write + FCM push
 * together, which isn't a simple data-access operation).
 */
interface NotificationRepositoryInterface
{
    public function paginateFor(Model $notifiable, ?string $category, int $perPage, int $page, string $pageName): LengthAwarePaginator;

    public function unreadCountFor(Model $notifiable): int;

    public function findForOrFail(Model $notifiable, string $id): DatabaseNotification;

    public function markAsRead(DatabaseNotification $notification): void;

    public function markAllAsReadFor(Model $notifiable): void;

    public function delete(DatabaseNotification $notification): void;
}
