<?php

namespace App\Events\Supplier;

use App\Models\Supplier;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Selected implements SupplierEventInterface
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private Supplier $supplier;

    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public function supplier(): Supplier
    {
        return $this->supplier;
    }
}
