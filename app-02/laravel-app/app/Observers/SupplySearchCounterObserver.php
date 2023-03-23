<?php

namespace App\Observers;

use App\Models\SupplySearchCounter;
use Illuminate\Support\Str;

class SupplySearchCounterObserver
{
    public function creating(SupplySearchCounter $supplySearchCounter): void
    {
        $supplySearchCounter->uuid ??= Str::uuid();
    }
}
