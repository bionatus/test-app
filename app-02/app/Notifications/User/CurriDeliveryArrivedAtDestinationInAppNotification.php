<?php

namespace App\Notifications\User;

use App;
use App\Constants\Notifications;
use App\Constants\PushNotificationEventNames;
use App\Models\InternalNotification;
use App\Models\Order;
use App\Notifications\SendsPushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Lang;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class CurriDeliveryArrivedAtDestinationInAppNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    const INTERNAL_MESSAGE = 'Your Order PO :name has arrived.';
    const PUSH_MESSAGE     = 'Your Order PO :name has arrived.';
    const PUSH_TITLE       = 'Your Order is here';
    protected Order                 $order;
    protected string                $internalMessage;
    protected string                $pushMessage;
    protected ?InternalNotification $internalNotification;

    public function __construct(Order $order)
    {
        $this->order                = $order;
        $this->internalMessage      = Lang::get(self::INTERNAL_MESSAGE, ['name' => $order->name]);
        $this->pushMessage          = Lang::get(self::PUSH_MESSAGE, ['name' => $order->name]);
        $this->internalNotification = $this->createInternalNotification();
        $this->onConnection('database');
    }

    public function via($notifiable)
    {
        if ($this->order->user->shouldSendPushNotificationWithoutSetting()) {
            return [Notifications::VIA_FCM];
        }

        return [];
    }

    public function toFcm(): FcmMessage
    {
        $notification = FcmNotification::create()->setTitle(self::PUSH_TITLE)->setBody($this->pushMessage);

        $message = $this->message($notification);
        $message->setData([
            'source' => json_encode([
                'event'                    => PushNotificationEventNames::AT_DESTINATION,
                'type'                     => Order::MORPH_ALIAS,
                'id'                       => $this->order->getRouteKey(),
                'internal_notification_id' => $this->internalNotification->getRouteKey(),
            ]),
        ]);

        return $message;
    }

    private function createInternalNotification(): ?InternalNotification
    {
        $user = $this->order->user;

        if (!$user->disabled_at) {
            /** @var InternalNotification $internalNotification */
            $internalNotification = $user->internalNotifications()->create([
                'message'      => $this->internalMessage,
                'source_event' => PushNotificationEventNames::AT_DESTINATION,
                'source_type'  => Order::MORPH_ALIAS,
                'source_id'    => $this->order->getRouteKey(),
            ]);

            return $internalNotification;
        }

        return null;
    }
}
