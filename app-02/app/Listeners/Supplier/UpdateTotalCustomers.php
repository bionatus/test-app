<?php

namespace App\Listeners\Supplier;

use App;
use App\Events\Supplier\SupplierEventInterface;
use App\Jobs\Supplier\UpdateTotalCustomers as UpdateTotalCustomersJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateTotalCustomers implements ShouldQueue
{
    public function handle(SupplierEventInterface $event)
    {
        $supplier = $event->supplier();

        UpdateTotalCustomersJob::dispatch($supplier);
    }
}
