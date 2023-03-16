<?php

namespace App\Models\Part\Scopes;

use App\Models\Part;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class FunctionalFirst implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderByRaw('type = "' . Part::TYPE_OTHER . '"');
    }
}
