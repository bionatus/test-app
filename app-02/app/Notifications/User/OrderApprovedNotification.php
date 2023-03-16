<?php

namespace App\Notifications\User;

use App;
use App\Constants\Notifications;
use App\Constants\PushNotificationEventNames;
use App\Models\InternalNotification;
use App\Models\Order;
use App\Models\Setting;
use App\Notifications\SendsPushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class OrderApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    const DELIVERY          = 'delivery';
    const PICKUP            = 'pickup';
    const PUSH_MESSAGE      = 'Your approved order has been received and is being worked on for %s.';
    const PUSH_SETTING_SLUG = Setting::SLUG_ORDER_APPROVED_RECEIVED_BY_SUPPLIER_IN_APP;
    const PUSH_TITLE        = 'Order Approved';
    const SMS_MESSAGE       = 'Bluon - Your approved order has been received and is being worked on for %s. Do Not Reply to this text.';
    const SMS_SETTING_SLUG  = Setting::SLUG_ORDER_APPROVED_RECEIVED_BY_SUPPLIER_SMS;
    const SOURCE_EVENT      = PushNotificationEventNames::APPROVED;
    const SOURCE_TYPE       = Order::MORPH_ALIAS;
    protected Order                 $order;
    protected ?InternalNotification $internalNotification;

    public function __construct(Order $order, InternalNotification $internalNotification)
    {
        $this->internalNotification = $internalNotification;
        $this->order                = $order;
        $this->onConnection('database');
    }

    public function via($notifiable)
    {
        $user = $this->order->user;
        $via  = [];

        if ($user->shouldSendInAppNotification(self::PUSH_SETTING_SLUG)) {
            $via[] = Notifications::VIA_FCM;
        }

        if ($user->shouldSendSmsNotification(self::SMS_SETTING_SLUG)) {
            $via[] = TwilioChannel::class;
        }

        return $via;
    }

    public function toTwilio($notifiable)
    {
        $orderDelivery = $this->order->orderDelivery;
        $deliveryType  = ($orderDelivery->isPickup()) ? self::PICKUP : self::DELIVERY;
        $smsMessage    = sprintf(self::SMS_MESSAGE, $deliveryType);

        return (new TwilioSmsMessage())->content($smsMessage);
    }

    public function toFcm(): FcmMessage
    {
        $orderDelivery    = $this->order->orderDelivery;
        $deliveryType     = ($orderDelivery->isPickup()) ? self::PICKUP : self::DELIVERY;
        $notificationBody = sprintf(self::PUSH_MESSAGE, $deliveryType);
        $notification     = FcmNotification::create()->setTitle(self::PUSH_TITLE)->setBody($notificationBody);

        $message = $this->message($notification);
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
}
