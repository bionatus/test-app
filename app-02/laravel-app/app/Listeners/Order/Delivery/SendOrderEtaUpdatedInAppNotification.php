<?php

namespace App\Listeners\Order\Delivery;

use App\Events\Order\OrderEventInterface;
use App\Notifications\User\OrderEtaUpdatedInAppNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderEtaUpdatedInAppNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderEventInterface $event)
    {
        $order = $event->order();
        $user  = $order->user;

        if ($user) {
            $user->notify(new OrderEtaUpdatedInAppNotification($order));
        }
    }
}
