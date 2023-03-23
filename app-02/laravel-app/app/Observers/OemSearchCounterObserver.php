<?php

namespace App\Observers;

use App\Models\OemSearchCounter;
use Illuminate\Support\Str;

class OemSearchCounterObserver
{
    public function creating(OemSearchCounter $oemSearchCounter): void
    {
        $oemSearchCounter->uuid ??= Str::uuid();
    }
}
