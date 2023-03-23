<?php

namespace App\Observers;

use App\Models\Oem;
use Illuminate\Support\Str;

class OemObserver
{
    public function creating(Oem $oem): void
    {
        $oem->uuid ??= Str::uuid();
    }
}
