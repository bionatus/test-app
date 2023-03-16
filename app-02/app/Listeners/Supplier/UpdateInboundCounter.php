<?php

namespace App\Listeners\Supplier;

use App;
use App\Events\Order\OrderEventInterface;
use App\Jobs\Supplier\UpdateInboundCounter as UpdateInboundCounterJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateInboundCounter implements ShouldQueue
{
    public function handle(OrderEventInterface $event)
    {
        $supplier = $event->order()->supplier;
        
        UpdateInboundCounterJob::dispatch($supplier);
    }
}
