<?php

namespace App\Listeners\Order;

use App\Events\Order\PointsEarned;
use App\Notifications\User\OrderPointsEarnedSmsNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPointsEarnedSmsNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PointsEarned $event)
    {
        $order = $event->order();
        $user  = $order->user;

        if ($user) {
            $user->notify(new OrderPointsEarnedSmsNotification($order));
        }
    }
}
