<?php

namespace App\Events\Order\ItemOrder;

use App\Models\ItemOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class ItemOrderEvent implements ItemOrderEventInterface
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected ItemOrder $itemOrder;

    public function __construct(ItemOrder $itemOrder)
    {
        $this->itemOrder = $itemOrder;
    }

    public function itemOrder(): ItemOrder
    {
        return $this->itemOrder;
    }
}
