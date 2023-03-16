<?php

namespace App\Models\Oem\Scopes;

use App\Models\Oem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Live implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('status', Oem::STATUS_LIVE);
    }
}
