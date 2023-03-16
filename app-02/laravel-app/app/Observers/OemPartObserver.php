<?php

namespace App\Observers;

use App\Models\OemPart;
use Illuminate\Support\Str;

class OemPartObserver
{
    public function creating(OemPart $oemPart): void
    {
        $oemPart->uid ??= Str::uuid();
    }
}
