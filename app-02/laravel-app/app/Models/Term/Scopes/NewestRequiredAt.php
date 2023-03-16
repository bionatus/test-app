<?php

namespace App\Models\Term\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class NewestRequiredAt implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderBy('required_at', 'desc');
    }
}
