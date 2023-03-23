<?php

namespace App\Events\Order;

use App\Models\Order;

interface OrderEventInterface
{
    public function __construct(Order $order);

    public function order(): Order;
}
