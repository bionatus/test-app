<?php

namespace App\Notifications\User;

use App;
use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Constants\InternalNotificationsSourceEvents;
use App\Constants\Notifications;
use App\Constants\PushNotificationEventNames;
use App\Models\InternalNotification;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Supplier;
use App\Notifications\SendsPushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Lang;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class AssignInAppNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    const INTERNAL_MESSAGE = 'Your have unread messages from :supplier_name. Tap here to view messages!';
    const PUSH_MESSAGE     = ':working_on_it is working on your quote. Stay tuned!';
    const SETTING_SLUG     = Setting::SLUG_SUPPLIER_IS_WORKING_IN_YOUR_ORDER_IN_APP;
    protected Order                 $order;
    protected string                $internalMessage;
    protected string                $pushMessage;
    protected string                $pushTitle;
    protected ?InternalNotification $internalNotification;

    public function __construct(Order $order)
    {
        $this->internalMessage      = Lang::get(self::INTERNAL_MESSAGE, ['supplier_name' => $order->supplier->name]);
        $this->pushMessage          = Lang::get(self::PUSH_MESSAGE, ['working_on_it' => $order->working_on_it]);
        $this->pushTitle            = $order->supplier->name;
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
        $notification = FcmNotification::create()->setTitle($this->pushTitle)->setBody($this->pushMessage);
        $message      = $this->message($notification);

        $message->setData([
            'source' => json_encode([
                'event'                    => PushNotificationEventNames::ASSIGNED,
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
            $supplier = $this->order->supplier;
            $channel  = App::make(GetPubnubChannel::class, ['supplier' => $supplier, 'user' => $user])->execute();

            /** @var InternalNotification $internalNotification */
            $internalNotification = $user->internalNotifications()->create([
                'message'      => $this->internalMessage,
                'source_event' => InternalNotificationsSourceEvents::NEW_MESSAGE,
                'source_type'  => Supplier::MORPH_ALIAS,
                'source_id'    => $supplier->getRouteKey(),
                'data'         => [
                    'channel_id'    => $channel->channel,
                    'supplier_data' => ['id' => $supplier->getRouteKey()],
                ],
            ]);

            return $internalNotification;
        }

        return null;
    }
}
