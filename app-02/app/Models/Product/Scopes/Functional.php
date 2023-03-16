<?php

namespace App\Models\Product\Scopes;

use App\Models\Part;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Functional implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereIn('type', Part::FUNCTIONAL_TYPES);
    }
}
