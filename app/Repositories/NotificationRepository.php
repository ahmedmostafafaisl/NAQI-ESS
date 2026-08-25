<?php

namespace App\Repositories;

use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function paginateFor(Model $notifiable, ?string $category, int $perPage, int $page, string $pageName): LengthAwarePaginator
    {
        return $notifiable->notifications()
            ->when($category, fn($q) => $q->where('data->category', $category))
            ->latest()
            ->paginate(perPage: $perPage, page: $page, pageName: $pageName);
    }

    public function unreadCountFor(Model $notifiable): int
    {
        return $notifiable->unreadNotifications()->count();
    }

    public function findForOrFail(Model $notifiable, string $id): DatabaseNotification
    {
        return $notifiable->notifications()->findOrFail($id);
    }

    public function markAsRead(DatabaseNotification $notification): void
    {
        $notification->markAsRead();
    }

    public function markAllAsReadFor(Model $notifiable): void
    {
        $notifiable->unreadNotifications->markAsRead();
    }

    public function delete(DatabaseNotification $notification): void
    {
        $notification->delete();
    }
}
