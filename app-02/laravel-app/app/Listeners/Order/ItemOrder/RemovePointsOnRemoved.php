<?php

namespace App\Listeners\Order\ItemOrder;

use App\Events\Order\ItemOrder\ItemOrderEventInterface;
use App\Models\Order;
use App\Models\Point;
use App\Models\Point\Scopes\ByAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RemovePointsOnRemoved implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ItemOrderEventInterface $event)
    {
        $itemOrder = $event->itemOrder();
        $order     = $itemOrder->order;
        $user      = $order->user;
        /** @var Point $points */
        $points = $order->points()->scoped(new ByAction(Point::ACTION_ORDER_APPROVED))->first();

        if (!$user || !$points) {
            return;
        }

        $user->points()->create([
            'object_id'     => $itemOrder->order->getKey(),
            'action'        => Point::ACTION_ORDER_ITEM_REMOVED,
            'object_type'   => Order::MORPH_ALIAS,
            'coefficient'   => $points->coefficient,
            'multiplier'    => $points->multiplier,
            'points_earned' => floor($itemOrder->price * $itemOrder->quantity * $points->coefficient * $points->multiplier) * -1,
        ]);

        $user->processLevel();
    }
}
