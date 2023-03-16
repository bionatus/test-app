<?php

namespace App\Models\AgentCall\Scopes;

use App\Models\AgentCall;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Completed implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where("{$model->getTable()}.status", AgentCall::STATUS_COMPLETED);
    }
}
