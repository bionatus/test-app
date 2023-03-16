<?php

namespace App\Models\Series\Scopes;

use App\Models\Scopes\Published;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Active implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->scoped(new Published());
        $builder->whereHas('brand', function(Builder $builder) {
            $builder->scoped(new Published());
        });
    }
}
