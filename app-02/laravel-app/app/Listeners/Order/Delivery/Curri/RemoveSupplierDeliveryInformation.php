<?php

namespace App\Listeners\Order\Delivery\Curri;

use App;
use App\Events\Order\OrderEventInterface;
use App\Jobs\Order\Delivery\Curri\RemoveSupplierDeliveryInformation as RemoveDeliveryInformationJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemoveSupplierDeliveryInformation implements ShouldQueue
{
    public function handle(OrderEventInterface $event)
    {
        RemoveDeliveryInformationJob::dispatch($event->order());
    }
}
