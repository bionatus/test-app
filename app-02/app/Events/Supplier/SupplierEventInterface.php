<?php

namespace App\Events\Supplier;

use App\Models\Supplier;

interface SupplierEventInterface
{
    public function supplier(): Supplier;
}
