<?php

namespace App\Models\User\Scopes;

use App\Models\Scopes\Alphabetically as BaseAlphabetically;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Alphabetically implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->scoped(new BaseAlphabetically('first_name'))->scoped(new BaseAlphabetically('last_name'));
    }
}
