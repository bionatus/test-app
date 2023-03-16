<?php

namespace App\Models\OrderInvoice\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class NotProcessed implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->whereNull('processed_at');
    }
}
