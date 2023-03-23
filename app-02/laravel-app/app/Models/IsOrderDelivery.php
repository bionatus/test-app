<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;

trait IsOrderDelivery
{
    public function orderDelivery(): HasOne
    {
        return $this->hasOne(OrderDelivery::class, 'id');
    }
}
