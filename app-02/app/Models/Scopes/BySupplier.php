<?php

namespace App\Models\Scopes;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BySupplier implements Scope
{
    private Supplier $supplier;

    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('supplier_id', $this->supplier->getKey());
    }
}
