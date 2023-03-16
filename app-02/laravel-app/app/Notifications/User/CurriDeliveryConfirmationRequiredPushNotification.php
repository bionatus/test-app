<?php

namespace App\Notifications\User;

use App;
use App\Constants\Notifications;
use App\Constants\PushNotificationEventNames;
use App\Models\Order;
use App\Notifications\SendsPushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Lang;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class CurriDeliveryConfirmationRequiredPushNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    const PUSH_MESSAGE = 'PO :name is ready to be picked up and delivered. Please confirm your delivery details.';
    const PUSH_TITLE   = 'Confirm Your Delivery';
    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
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
        $notificationBody = Lang::get(self::PUSH_MESSAGE, ['name' => $this->order->name]);
        $notification     = FcmNotification::create()->setTitle(self::PUSH_TITLE)->setBody($notificationBody);

        $message = $this->message($notification);
        $message->setData([
            'source' => json_encode([
                'event' => PushNotificationEventNames::CONFIRM_DELIVERY,
                'type'  => Order::MORPH_ALIAS,
                'id'    => $this->order->getRouteKey(),
            ]),
        ]);

        return $message;
    }
}
