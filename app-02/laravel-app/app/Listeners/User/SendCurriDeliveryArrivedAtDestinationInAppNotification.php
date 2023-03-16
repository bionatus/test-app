<?php

namespace App\Listeners\User;

use App;
use App\Events\Order\Delivery\Curri\ArrivedAtDestination as ArrivedAtDestinationEvent;
use App\Notifications\User\CurriDeliveryArrivedAtDestinationInAppNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCurriDeliveryArrivedAtDestinationInAppNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ArrivedAtDestinationEvent $event)
    {
        $order = $event->order();
        $user  = $order->user;

        if ($user) {
            $user->notify(new CurriDeliveryArrivedAtDestinationInAppNotification($order));
        }
    }
}
