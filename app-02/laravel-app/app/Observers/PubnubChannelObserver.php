<?php

namespace App\Observers;

use App\Models\PubnubChannel;

class PubnubChannelObserver
{
    public function creating(PubnubChannel $pubnubChannel): void
    {
        $supplierRouteKey       = $pubnubChannel->supplier->getRouteKey();
        $userRouteKey           = $pubnubChannel->user->getRouteKey();
        $pubnubChannel->channel = "supplier-{$supplierRouteKey}.user-{$userRouteKey}";
    }
}
