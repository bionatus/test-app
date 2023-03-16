<?php

namespace App\Listeners\Order\Delivery\Curri;

use App;
use App\Events\Order\OrderEventInterface;
use App\Jobs\Order\Delivery\Curri\SetUserDeliveryInformation as SetUserDeliveryInformationJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SetUserDeliveryInformation implements ShouldQueue
{
    public function handle(OrderEventInterface $event)
    {
        SetUserDeliveryInformationJob::dispatch($event->order());
    }
}
