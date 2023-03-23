<?php

namespace App\Models\OrderInvoice\Scopes;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByOrderSupplier implements Scope
{
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function apply(Builder $builder, Model $model)
    {
        $order = $this->order;
        $builder->whereHas('order', function($query) use ($order) {
            $query->where('supplier_id', $order->supplier_id);
        });
    }
}
