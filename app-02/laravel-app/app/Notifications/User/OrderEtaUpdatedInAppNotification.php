<?php

namespace App\Notifications\User;

use App;
use App\Constants\PushNotificationEventNames;
use App\Models\InternalNotification;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class OrderEtaUpdatedInAppNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const INTERNAL_MESSAGE = 'Your quote :order_name ETA has been updated by :supplier_name. New ETA: :eta_updated.';
    const SOURCE_EVENT     = PushNotificationEventNames::ETA_UPDATED;
    const SOURCE_TYPE      = Order::MORPH_ALIAS;
    protected Order                 $order;
    protected ?InternalNotification $internalNotification;

    public function __construct(Order $order)
    {
        $this->order                = $order;
        $this->internalNotification = $this->createInternalNotification();
        $this->onConnection('database');
    }

    public function via()
    {
        return [];
    }

    private function createInternalNotification(): ?InternalNotification
    {
        $user = $this->order->user;

        if (!$user->disabled_at) {
            $internalMessage = Lang::get(self::INTERNAL_MESSAGE, [
                'order_name'    => $this->order->name ?? null,
                'supplier_name' => $this->order->supplier->name,
                'eta_updated'   => $this->order->orderDelivery->date->format('m-d-y') . ", " . $this->order->orderDelivery->time_range,

            ]);

            /** @var InternalNotification $internalNotification */
            $internalNotification = $this->order->user->internalNotifications()->create([
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
