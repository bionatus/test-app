<?php

namespace App\Events\Order\Delivery\Curri;

use App\Models\CurriDelivery;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OnRoute
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private CurriDelivery $curriDelivery;

    public function __construct(CurriDelivery $curriDelivery)
    {
        $this->curriDelivery = $curriDelivery;
    }

    public function curriDelivery(): CurriDelivery
    {
        return $this->curriDelivery;
    }
}
