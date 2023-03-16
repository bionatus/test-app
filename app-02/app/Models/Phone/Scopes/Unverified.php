<?php

namespace App\Models\Phone\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Unverified implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereNull('verified_at');
    }
}
