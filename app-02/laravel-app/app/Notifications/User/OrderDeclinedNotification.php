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

class OrderDeclinedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    const MESSAGE           = '%s has declined to give you a quote for your order request. Tap here to view!';
    const PUSH_SETTING_SLUG = Setting::SLUG_ORDER_IS_CANCELED_IN_APP;
    const PUSH_TITLE        = 'Order Declined';
    const SMS_MESSAGE       = 'Bluon - %s has declined to give you a quote for your order request. Do Not Reply to this text.';
    const SMS_SETTING_SLUG  = Setting::SLUG_ORDER_IS_CANCELED_SMS;
    const SOURCE_EVENT      = PushNotificationEventNames::DECLINED;
    const SOURCE_TYPE       = Order::MORPH_ALIAS;
    protected Order                 $order;
    private string                  $message;
    protected ?InternalNotification $internalNotification;

    public function __construct(Order $order)
    {
        $this->message              = sprintf(self::MESSAGE, $order->supplier->name);
        $this->order                = $order;
        $this->internalNotification = $this->createInternalNotification();
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
        $smsMessage = sprintf(self::SMS_MESSAGE, $this->order->supplier->name);

        return (new TwilioSmsMessage())->content($smsMessage);
    }

    public function toFcm(): FcmMessage
    {
        $notification = FcmNotification::create()->setTitle(self::PUSH_TITLE)->setBody($this->message);
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

    private function createInternalNotification(): ?InternalNotification
    {
        $user = $this->order->user;

        if (!$user->disabled_at) {
            /** @var InternalNotification $internalNotification */
            $internalNotification = $user->internalNotifications()->create([
                'message'      => $this->message,
                'source_event' => self::SOURCE_EVENT,
                'source_type'  => self::SOURCE_TYPE,
                'source_id'    => $this->order->getRouteKey(),
            ]);

            return $internalNotification;
        }

        return null;
    }
}
