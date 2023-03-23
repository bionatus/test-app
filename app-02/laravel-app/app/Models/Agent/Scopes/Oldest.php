<?php

namespace App\Models\Agent\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Oldest implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->oldest($model->getKeyName());
    }
}
