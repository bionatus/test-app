<?php

namespace App\Models\Order\Scopes;

use App\Models\Substatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class PriceAndAvailabilityRequests implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->scoped(new ByLastSubstatuses(Substatus::STATUSES_PENDING));
    }
}
