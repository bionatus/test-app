<?php

namespace App\Listeners\Order\Delivery\Curri;

use App;
use App\Events\Order\OrderEventInterface;
use App\Jobs\Order\Delivery\Curri\SetDeliverySupplierInformation as SetDeliverySupplierInformationJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SetDeliverySupplierInformation implements ShouldQueue
{
    public function handle(OrderEventInterface $event)
    {
        SetDeliverySupplierInformationJob::dispatch($event->order());
    }
}
