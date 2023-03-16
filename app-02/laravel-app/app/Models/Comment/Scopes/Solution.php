<?php

namespace App\Models\Comment\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Solution implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('solution', 1);
    }
}
