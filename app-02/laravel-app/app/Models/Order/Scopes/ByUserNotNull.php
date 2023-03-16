<?php

namespace App\Models\Order\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByUserNotNull implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->whereNotNull('user_id');
    }
}
