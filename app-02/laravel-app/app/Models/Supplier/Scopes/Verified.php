<?php

namespace App\Models\Supplier\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Verified implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->whereNotNull('verified_at');
    }
}
