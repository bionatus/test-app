<?php

namespace App\Notifications;

use App\Constants\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmMessage;

class UnreadNotificationCountUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    private int $unreadNotificationsCount;

    public function __construct(int $unreadNotificationsCount)
    {
        $this->unreadNotificationsCount = $unreadNotificationsCount;
        $this->onConnection('database');
        $this->onQueue(Notifications::QUEUE_FCM);
    }

    public function toFcm(): FcmMessage
    {
        $message = FcmMessage::create();
        $message->setData([
            'type'     => 'resource',
            'resource' => json_encode([
                'notifications_count' => $this->unreadNotificationsCount,
            ]),
        ]);

        return $message;
    }
}
