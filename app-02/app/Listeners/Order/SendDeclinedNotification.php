<?php

namespace App\Listeners\Order;

use App\Events\Order\Declined;
use App\Notifications\User\OrderDeclinedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendDeclinedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Declined $event)
    {
        $order = $event->order();
        $user  = $order->user;

        if ($user) {
            $user->notify(new OrderDeclinedNotification($order));
        }
    }
}
