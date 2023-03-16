<?php

namespace App\Models\Phone\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Verified implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereNotNull('verified_at');
    }
}
