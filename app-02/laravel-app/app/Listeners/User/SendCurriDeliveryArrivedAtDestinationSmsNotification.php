<?php

namespace App\Listeners\User;

use App;
use App\Events\Order\Delivery\Curri\ArrivedAtDestination as ArrivedAtDestinationEvent;
use App\Notifications\User\CurriDeliveryArrivedAtDestinationSmsNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCurriDeliveryArrivedAtDestinationSmsNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ArrivedAtDestinationEvent $event)
    {
        $order = $event->order();
        $user  = $order->user;

        if ($user) {
            $user->notify(new CurriDeliveryArrivedAtDestinationSmsNotification($order));
        }
    }
}
