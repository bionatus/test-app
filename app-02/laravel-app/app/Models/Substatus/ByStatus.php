<?php

namespace App\Models\Substatus;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByStatus implements Scope
{
    private int $status;

    public function __construct(int $status)
    {
        $this->status = $status;
    }

    public function apply(Builder $builder, Model $model)
    {
        $builder->where('status_id', $this->status);
    }
}
