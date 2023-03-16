<?php

namespace App\Models\Activity\Scopes;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Latest implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->latest(Activity::CREATED_AT)->latest($model->getKeyName());
    }
}
