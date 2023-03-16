<?php

namespace App\Models\Replacement\Scopes;

use App\Models\Replacement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SingleTypeFirst implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderByRaw(Replacement::tableName() . '.type = "' . Replacement::TYPE_SINGLE . '" DESC');
    }
}
