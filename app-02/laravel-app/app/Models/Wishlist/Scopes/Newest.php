<?php

namespace App\Models\Wishlist\Scopes;

use App\Models\Scopes\Newest as BaseNewest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Newest implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        (new BaseNewest())->apply($builder, $model);
    }
}
