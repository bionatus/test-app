<?php

namespace App\Listeners\Supplier;

use App;
use App\Events\Order\OrderEventInterface;
use App\Jobs\Supplier\UpdateOutboundCounter as UpdateOutboundCounterJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateOutboundCounter implements ShouldQueue
{
    public function handle(OrderEventInterface $event)
    {
        $supplier = $event->order()->supplier;
        
        UpdateOutboundCounterJob::dispatch($supplier);
    }
}
