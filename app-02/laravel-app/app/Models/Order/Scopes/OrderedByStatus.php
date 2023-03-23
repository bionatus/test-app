<?php

namespace App\Models\Order\Scopes;

use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Status;
use App\Models\Substatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OrderedByStatus implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $order          = Order::tableName();
        $orderSubstatus = OrderSubstatus::tableName();
        $substatus      = Substatus::tableName();

        $builder->select("$order.*", "$orderSubstatus.order_id")
            ->leftJoin($orderSubstatus, function($builder) use ($order, $orderSubstatus) {
                $builder->on("$orderSubstatus.order_id", '=', "$order.id")
                    ->whereRaw("$orderSubstatus.id IN (select MAX(os2.id) from $orderSubstatus as os2 join $order as o2 on o2.id = os2.order_id group by o2.id)");
            })
            ->join($substatus, "$substatus.id", "$orderSubstatus.substatus_id", 'LEFT')
            ->orderByRaw("$substatus.status_id = " . Status::STATUS_APPROVED . " DESC")
            ->orderByRaw("$substatus.status_id = " . Status::STATUS_COMPLETED . " DESC")
            ->orderByRaw("$substatus.status_id = " . Status::STATUS_CANCELED . " DESC");
    }
}
