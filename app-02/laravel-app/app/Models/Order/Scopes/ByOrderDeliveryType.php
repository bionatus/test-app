<?php

namespace App\Models\Order\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByOrderDeliveryType implements Scope
{
    private string $orderDeliveryType;

    public function __construct(string $orderDeliveryType)
    {
        $this->orderDeliveryType = $orderDeliveryType;
    }

    public function apply(Builder $builder, Model $model)
    {
        $builder->whereHas('orderDelivery', function(Builder $builder) {
            $builder->where('type', $this->orderDeliveryType);
        });
    }
}
