<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

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
        $channels = ['database'];

        if (! empty($notifiable->fcm_token)) {
            $channels[] = FcmChannel::class;
        }

        return $channels;
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

    public function toFcm($notifiable): FcmMessage
    {
        return (new FcmMessage(notification: new FcmNotification(
            title: $this->title,
            body: $this->body,
        )))->data(array_merge($this->data, ['category' => $this->category]));
    }
}
