<?php

namespace App\Notifications\User;

use App;
use App\Constants\Notifications;
use App\Constants\PushNotificationEventNames;
use App\Models\InternalNotification;
use App\Models\Order;
use App\Models\User;
use App\Notifications\SendsPushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class CurriDeliveryOnRoutePushNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    const PUSH_MESSAGE = 'Your driver is on the way!';
    const PUSH_TITLE   = 'New Message';
    const SOURCE_EVENT = PushNotificationEventNames::ON_ROUTE;
    const SOURCE_TYPE  = Order::MORPH_ALIAS;
    private Order                 $order;
    private ?InternalNotification $internalNotification;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->onConnection('database');
    }

    public function via($notifiable)
    {
        $this->internalNotification = $this->createInternalNotification($notifiable);

        if ($notifiable->shouldSendPushNotificationWithoutSetting()) {
            return [Notifications::VIA_FCM];
        }

        return [];
    }

    public function toFcm(): FcmMessage
    {
        $notification = FcmNotification::create()->setTitle(self::PUSH_TITLE)->setBody(self::PUSH_MESSAGE);
        $message      = $this->message($notification);

        $message->setData([
            'source' => json_encode([
                'event'                    => self::SOURCE_EVENT,
                'type'                     => self::SOURCE_TYPE,
                'id'                       => $this->order->getRouteKey(),
                'internal_notification_id' => $this->internalNotification->getRouteKey(),
            ]),
        ]);

        return $message;
    }

    private function createInternalNotification(User $user): ?InternalNotification
    {
        if (!$user->disabled_at) {
            /** @var InternalNotification $internalNotification */
            $internalNotification = $user->internalNotifications()->create([
                'message'      => self::PUSH_MESSAGE,
                'source_event' => self::SOURCE_EVENT,
                'source_type'  => self::SOURCE_TYPE,
                'source_id'    => $this->order->getRouteKey(),
            ]);

            return $internalNotification;
        }

        return null;
    }
}
