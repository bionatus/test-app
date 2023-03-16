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

class ApprovedByTeamInAppNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    const INTERNAL_MESSAGE = 'Your team has approved your quote via shared link. Tap here to view!';
    const PUSH_MESSAGE     = 'Your team has approved your quote via shared link.';
    const PUSH_TITLE       = 'Quote Approved';
    const SETTING_SLUG     = Setting::SLUG_ORDER_APPROVED_BY_YOUR_TEAM_IN_APP;
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
        $notificationBody = self::PUSH_MESSAGE;
        $notification     = FcmNotification::create()->setTitle(self::PUSH_TITLE)->setBody($notificationBody);

        $message = $this->message($notification);
        $message->setData([
            'source' => json_encode([
                'event'                    => PushNotificationEventNames::APPROVED,
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
            $internalNotification = $this->order->user->internalNotifications()->create([
                'message'      => self::INTERNAL_MESSAGE,
                'source_event' => PushNotificationEventNames::APPROVED,
                'source_type'  => Order::MORPH_ALIAS,
                'source_id'    => $this->order->getRouteKey(),
            ]);

            return $internalNotification;
        }

        return null;
    }
}
