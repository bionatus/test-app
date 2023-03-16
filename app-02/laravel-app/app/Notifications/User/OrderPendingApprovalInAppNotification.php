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
use Illuminate\Support\Facades\Lang;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class OrderPendingApprovalInAppNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    const INTERNAL_MESSAGE = 'Quote alert! Approve quote to receive :total_points_earned points from :supplier_name or decline. Tap here to view.';
    const PUSH_MESSAGE     = 'Quote Alert! Be sure to approve the Quote from :supplier_name to receive :total_points_earned Points.';
    const SETTING_SLUG     = Setting::SLUG_ORDER_PENDING_APPROVAL_IN_APP;
    protected Order                 $order;
    protected string                $message;
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
            'supplier_name'       => $this->order->supplier->name,
            'total_points_earned' => $this->getOrderPoints(),
        ]);
        $notification = FcmNotification::create()->setBody($pushMessage);
        $message      = $this->message($notification);

        $message->setData([
            'source' => json_encode([
                'event'                    => PushNotificationEventNames::PENDING_APPROVAL,
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
                'supplier_name'       => $this->order->supplier->name,
                'total_points_earned' => $this->getOrderPoints(),
            ]);

            /** @var InternalNotification $internalNotification */
            $internalNotification = $user->internalNotifications()->create([
                'message'      => $internalMessage,
                'source_event' => PushNotificationEventNames::PENDING_APPROVAL,
                'source_type'  => Order::MORPH_ALIAS,
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
