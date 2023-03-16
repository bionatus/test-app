<?php

namespace App\Observers;

use App\Models\XoxoRedemption;
use Illuminate\Support\Str;

class XoxoRedemptionObserver
{
    public function creating(XoxoRedemption $xoxoRedemption): void
    {
        $xoxoRedemption->uuid = Str::uuid();
    }
}
