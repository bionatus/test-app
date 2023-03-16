<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Latest implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $latest = $model::CREATED_AT;

        $builder->latest("{$model->getTable()}.{$latest}")->latest($model->getKeyName());
    }
}
