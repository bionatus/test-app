<?php

namespace App\Models\AppNotification\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Unread implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('read', false);
    }
}
