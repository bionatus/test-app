<?php

namespace App\Models\Part\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Number implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderBy('number');
    }
}
