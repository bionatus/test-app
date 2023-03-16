<?php

namespace App\Observers;

use App\Models\PartSearchCounter;
use Illuminate\Support\Str;

class PartSearchCounterObserver
{
    public function creating(PartSearchCounter $partSearchCounter): void
    {
        $partSearchCounter->uuid ??= Str::uuid();
    }
}
