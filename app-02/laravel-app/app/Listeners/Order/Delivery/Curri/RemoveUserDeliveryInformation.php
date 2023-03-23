<?php

namespace App\Listeners\Order\Delivery\Curri;

use App;
use App\Events\Order\OrderEventInterface;
use App\Jobs\Order\Delivery\Curri\RemoveUserDeliveryInformation as RemoveDeliveryInformationJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemoveUserDeliveryInformation implements ShouldQueue
{
    public function handle(OrderEventInterface $event)
    {
        $order = $event->order();
        if ($order->orderDelivery->isCurriDelivery()) {
            RemoveDeliveryInformationJob::dispatch($order);
        }
    }
}
