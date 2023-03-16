<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class NewestUpdated implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $updatedAt = $model::UPDATED_AT;

        $builder->latest("{$model->getTable()}.{$updatedAt}")->latest($model->getKeyName());
    }
}
