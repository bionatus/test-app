<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Newest implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $createdAt = $model::CREATED_AT;

        $builder->orderByDesc($createdAt);
    }
}
