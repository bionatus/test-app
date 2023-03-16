<?php

namespace App\Listeners\Order\Delivery\Curri;

use App;
use App\Events\Order\OrderEvent;
use App\Jobs\Order\DelayComplete;
use App\Models\CurriDelivery;
use App\Models\Substatus;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DelayCompleteDoneJob implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderEvent $event)
    {
        $order         = $event->order();
        $orderDelivery = $order->orderDelivery;
        if ($orderDelivery->isCurriDelivery() && $order->lastStatus->substatus_id === Substatus::STATUS_APPROVED_DELIVERED) {
            $ttl = Config::get('order.autocomplete.curri_ttl');

            /** @var CurriDelivery $curriDelivery */
            $curriDelivery = $orderDelivery->deliverable;

            DelayComplete::dispatch($order)->delay($curriDelivery->updated_at->clone()->addMinutes($ttl));
        }
    }
}
