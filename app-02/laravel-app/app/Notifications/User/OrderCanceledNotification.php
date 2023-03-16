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
use Lang;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class OrderCanceledNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    const INTERNAL_MESSAGE  = 'Your order:order_name from :supplier_name has been cancelled. :total_points_lost Points were removed from your account.';
    const PUSH_MESSAGE      = 'PO:order_name has been cancelled. You lost :total_points_lost Points from your account.';
    const PUSH_SETTING_SLUG = Setting::SLUG_ORDER_IS_CANCELED_IN_APP;
    const PUSH_TITLE        = 'Order Cancelled';
    const SMS_MESSAGE       = "Bluon - Your order:order_name from :supplier_name has been cancelled. You lost :total_points_lost Points from your account.\nDo Not Reply to this text.";
    const SMS_SETTING_SLUG  = Setting::SLUG_ORDER_IS_CANCELED_SMS;
    const SOURCE_EVENT      = PushNotificationEventNames::CANCELED;
    const SOURCE_TYPE       = Order::MORPH_ALIAS;
    protected Order                 $order;
    protected int                   $missingPoints;
    protected ?InternalNotification $internalNotification;

    public function __construct(Order $order, int $missingPoints)
    {
        $this->order                = $order;
        $this->missingPoints        = $missingPoints;
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
        $smsMessage = Lang::get(self::SMS_MESSAGE, [
            'order_name'        => $this->order->name ? " PO {$this->order->name}" : null,
            'supplier_name'     => $this->order->supplier->name,
            'total_points_lost' => $this->missingPoints,
        ]);

        return (new TwilioSmsMessage())->content($smsMessage);
    }

    public function toFcm(): FcmMessage
    {
        $pushMessage  = Lang::get(self::PUSH_MESSAGE, [
            'order_name'        => $this->order->name ? " {$this->order->name}" : null,
            'total_points_lost' => $this->missingPoints,
        ]);
        $notification = FcmNotification::create()->setTitle(self::PUSH_TITLE)->setBody($pushMessage);
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
            $internalMessage = Lang::get(self::INTERNAL_MESSAGE, [
                'order_name'        => $this->order->name ? " PO #: {$this->order->name}" : null,
                'supplier_name'     => $this->order->supplier->name,
                'total_points_lost' => $this->missingPoints,
            ]);

            /** @var InternalNotification $internalNotification */
            $internalNotification = $user->internalNotifications()->create([
                'message'      => $internalMessage,
                'source_event' => self::SOURCE_EVENT,
                'source_type'  => self::SOURCE_TYPE,
                'source_id'    => $this->order->getRouteKey(),
            ]);

            return $internalNotification;
        }

        return null;
    }
}
