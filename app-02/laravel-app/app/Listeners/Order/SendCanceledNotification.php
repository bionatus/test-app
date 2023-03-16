<?php

namespace App\Listeners\Order;

use App\Events\Order\Canceled;
use App\Models\Point;
use App\Models\Point\Scopes\ByAction;
use App\Notifications\User\OrderCanceledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCanceledNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Canceled $event)
    {
        $order = $event->order();
        $user  = $order->user;
        /** @var Point $points */
        $points = $order->points()->scoped(new ByAction(Point::ACTION_ORDER_CANCELED))->first();

        if ($user && $points) {
            $missingPoints = abs($points->points_earned);
            $user->notify(new OrderCanceledNotification($order, $missingPoints));
        }
    }
}
