<?php

namespace App\Notifications\User;

use App;
use App\Actions\Models\Order\CalculatePoints;
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

class OrderSentForApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    const MESSAGE           = 'You just received a quote from :supplier_name. Get :total_points_earned Points once approved.';
    const PUSH_SETTING_SLUG = Setting::SLUG_ORDER_IS_READY_FOR_APPROVAL_IN_APP;
    const PUSH_TITLE        = 'New Quote';
    const SMS_MESSAGE       = "Bluon - :supplier_name sent a quote. Get :total_points_earned Points once approved for 2% cash back!\nOrder ready on :availability.\nDo not reply.";
    const SMS_SETTING_SLUG  = Setting::SLUG_ORDER_IS_READY_FOR_APPROVAL_SMS;
    const SOURCE_EVENT      = PushNotificationEventNames::SENT_FOR_APPROVAL;
    const SOURCE_TYPE       = Order::MORPH_ALIAS;
    protected Order                 $order;
    protected ?InternalNotification $internalNotification;
    private string                  $message;

    public function __construct(Order $order)
    {
        $this->order                = $order;
        $this->message              = Lang::get(self::MESSAGE, [
            'supplier_name'       => $order->supplier->name,
            'total_points_earned' => $this->getOrderPoints(),
        ]);
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
            'availability'        => $this->order->availabilityTranslation(),
            'supplier_name'       => $this->order->supplier->name,
            'total_points_earned' => $this->getOrderPoints(),
        ]);

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

    private function getOrderPoints(): int
    {
        $point = App::make(CalculatePoints::class, ['order' => $this->order])->execute();

        return $point->points();
    }
}
