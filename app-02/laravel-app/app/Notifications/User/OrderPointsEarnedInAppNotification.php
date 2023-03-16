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

class OrderPointsEarnedInAppNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    const INTERNAL_MESSAGE = 'Way to go! By approving :po_number, you earned :total_points_earned Points. You’ll lose them if you cancel.';
    const PUSH_MESSAGE     = 'Score! You just earned :total_points_earned Points! for approving PO :po_number.';
    const PUSH_TITLE       = 'Points Earned!';
    const SETTING_SLUG     = Setting::SLUG_BLUON_POINTS_EARNED_IN_APP;
    protected Order                 $order;
    protected ?InternalNotification $internalNotification;

    public function __construct(Order $order)
    {
        $this->order                = $order;
        $this->internalNotification = $this->createInternalNotification();
        $this->onConnection('database');
    }

    public function via($notifiable)
    {
        $user = $this->order->user;

        if ($user->shouldSendInAppNotification(self::SETTING_SLUG)) {
            return [Notifications::VIA_FCM];
        }

        return [];
    }

    public function toFcm(): FcmMessage
    {
        $pushMessage  = Lang::get(self::PUSH_MESSAGE, [
            'po_number'           => $this->order->name,
            'total_points_earned' => $this->order->totalPointsEarned(),
        ]);
        $notification = FcmNotification::create()->setTitle(self::PUSH_TITLE)->setBody($pushMessage);
        $message      = $this->message($notification);

        $message->setData([
            'source' => json_encode([
                'event'                    => PushNotificationEventNames::POINTS_EARNED,
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
            $internalMessage = Lang::get(self::INTERNAL_MESSAGE, [
                'po_number'           => $this->order->name,
                'total_points_earned' => $this->order->totalPointsEarned(),
            ]);

            /** @var InternalNotification $internalNotification */
            $internalNotification = $user->internalNotifications()->create([
                'message'      => $internalMessage,
                'source_event' => PushNotificationEventNames::POINTS_EARNED,
                'source_type'  => Order::MORPH_ALIAS,
                'source_id'    => $this->order->getRouteKey(),
            ]);

            return $internalNotification;
        }

        return null;
    }
}
