<?php

namespace App\Models\Supplier\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByPreferred implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('preferred', true);
    }
}
