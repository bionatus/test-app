<?php

namespace App\Models\Supplier\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByOrderInvoiceCreationMonth implements Scope
{
    private int $month;

    public function __construct(int $month)
    {
        $this->month = $month;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas('orders.orderInvoices', function($query) {
            $query->whereMonth('created_at', '=', $this->month);
        });
    }
}
