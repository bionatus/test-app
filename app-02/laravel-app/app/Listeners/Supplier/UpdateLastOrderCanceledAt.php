<?php

namespace App\Listeners\Supplier;

use App;
use App\Events\Order\OrderEventInterface;
use App\Jobs\Supplier\UpdateLastOrderCanceledAt as UpdateLastOrderCanceledAtJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateLastOrderCanceledAt implements ShouldQueue
{
    public function handle(OrderEventInterface $event)
    {
        $supplier = $event->order()->supplier;

        UpdateLastOrderCanceledAtJob::dispatch($supplier);
    }
}
