<?php

namespace App\Listeners\Supplier;

use App;
use App\Events\Supplier\SupplierEventInterface;
use App\Jobs\Supplier\UpdateCustomersCounter as UpdateCustomersCounterJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateCustomersCounter implements ShouldQueue
{
    public function handle(SupplierEventInterface $event)
    {
        UpdateCustomersCounterJob::dispatch($event->supplier());
    }
}
