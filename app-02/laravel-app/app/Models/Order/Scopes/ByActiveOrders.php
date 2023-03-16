<?php

namespace App\Models\Order\Scopes;

use App\Models\Substatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByActiveOrders implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas('lastStatus', function(Builder $builder) {
            $builder->where('substatus_id', '<', Substatus::STATUS_COMPLETED_DONE);
        });
    }
}
