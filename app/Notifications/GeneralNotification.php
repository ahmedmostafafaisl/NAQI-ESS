<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * In-app (database) notification record. Push delivery via FCM is handled
 * explicitly and separately by App\Services\FcmService, which gives us
 * real success/failure/invalid-token reporting instead of a silent channel.
 *
 * @see \App\Services\FcmService
 * @see \App\Services\NotificationService
 */
class GeneralNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public string $category = 'system',
        public array $data = [],
        public ?int $createdBy = null,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'category' => $this->category,
            'data' => $this->data,
            'created_by' => $this->createdBy,
        ];
    }
}
