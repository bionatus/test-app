<?php

namespace App\Models\Layout\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Highest implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->orderBy('version', 'DESC');
    }
}
