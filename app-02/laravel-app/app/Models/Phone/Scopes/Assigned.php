<?php

namespace App\Models\Phone\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Assigned implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereNotNull('user_id');
    }
}
