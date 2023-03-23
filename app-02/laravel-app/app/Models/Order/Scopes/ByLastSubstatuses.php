<?php

namespace App\Models\Order\Scopes;

use App\Models\OrderSubstatus\Scopes\BySubstatuses;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByLastSubstatuses implements Scope
{
    private array $statuses;

    public function __construct(array $statuses)
    {
        $this->statuses = $statuses;
    }

    public function apply(Builder $builder, Model $model)
    {
        $builder->whereHas('lastStatus', function(Builder $builder) {
            $builder->scoped(new BySubstatuses($this->statuses));
        });
    }
}
