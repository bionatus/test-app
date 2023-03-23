<?php

namespace App\Models\OrderSubstatus\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BySubstatuses implements Scope
{
    private array $statuses;

    public function __construct(array $statuses)
    {
        $this->statuses = $statuses;
    }

    public function apply(Builder $builder, Model $model)
    {
        $builder->whereIn('substatus_id', $this->statuses);
    }
}
