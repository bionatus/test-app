<?php

namespace App\Observers;

use App\Models\OrderDelivery;

class OrderDeliveryObserver
{
    public function saved(OrderDelivery $orderDelivery): void
    {
        $orderDelivery->order()->touch();
    }
}
