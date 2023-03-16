<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Oldest implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $latest = $model::CREATED_AT;

        $builder->oldest($latest)->oldest($model->getKeyName());
    }
}
