<?php

namespace App\Events\Order\ItemOrder;

use App\Models\ItemOrder;

interface ItemOrderEventInterface
{
    public function __construct(ItemOrder $itemOrder);

    public function itemOrder(): ItemOrder;
}
