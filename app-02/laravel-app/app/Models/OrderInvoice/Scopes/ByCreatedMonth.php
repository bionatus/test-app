<?php

namespace App\Models\OrderInvoice\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByCreatedMonth implements Scope
{
    private int $month;

    public function __construct(int $month)
    {
        $this->month = $month;
    }

    public function apply(Builder $builder, Model $model)
    {
        $builder->whereMonth('created_at', $this->month);
    }
}
