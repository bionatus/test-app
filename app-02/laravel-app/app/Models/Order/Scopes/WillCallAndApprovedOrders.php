<?php

namespace App\Models\Order\Scopes;

use App\Models\OrderDelivery;
use App\Models\Substatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class WillCallAndApprovedOrders implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->where(function(Builder $builder) {
            $builder->scoped(new ByLastSubstatuses([
                Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED,
                Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
                Substatus::STATUS_APPROVED_READY_FOR_DELIVERY,
            ]))->orWhere(function(Builder $builder) {
                $builder->scoped(new ByOrderDeliveryType(OrderDelivery::TYPE_PICKUP))
                    ->scoped(new ByLastSubstatuses([Substatus::STATUS_APPROVED_DELIVERED]));
            });
        });
    }
}
