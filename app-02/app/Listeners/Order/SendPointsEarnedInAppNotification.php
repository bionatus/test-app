<?php

namespace App\Listeners\Order;

use App\Events\Order\PointsEarned;
use App\Notifications\User\OrderPointsEarnedInAppNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPointsEarnedInAppNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PointsEarned $event)
    {
        $order = $event->order();
        $user  = $order->user;

        if ($user) {
            $user->notify(new OrderPointsEarnedInAppNotification($order));
        }
    }
}
