<?php

namespace App\Listeners\Order;

use App\Events\Order\OrderEventInterface;
use App\Models\Point;
use App\Models\Point\Scopes\ByAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RemovePointsOnCanceled implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderEventInterface $event)
    {
        $order = $event->order();
        $user  = $order->user;

        if ($user) {
            /** @var Point $points */
            $points = $order->points()->scoped(new ByAction(Point::ACTION_ORDER_APPROVED))->first();

            if ($points && $points->points_earned > 0) {
                $user->points()->create([
                    'object_id'     => $order->getKey(),
                    'action'        => Point::ACTION_ORDER_CANCELED,
                    'object_type'   => $order::MORPH_ALIAS,
                    'coefficient'   => $points->coefficient,
                    'multiplier'    => $points->multiplier,
                    'points_earned' => $order->totalPointsEarned() * -1,
                ]);
            }

            $user->processLevel();
        }
    }
}
