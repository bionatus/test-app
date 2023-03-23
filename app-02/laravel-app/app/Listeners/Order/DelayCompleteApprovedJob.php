<?php

namespace App\Listeners\Order;

use App\Events\Order\OrderEvent;
use App\Jobs\Order\CompleteApproved;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DelayCompleteApprovedJob implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderEvent $event)
    {
        $order = $event->order();

        if (!$order->orderDelivery->isCurriDelivery()) {
            $ttl = Config::get('order.autocomplete.ttl');

            CompleteApproved::dispatch($order)->delay($order->updated_at->clone()->addMinutes($ttl));
        }
    }
}
