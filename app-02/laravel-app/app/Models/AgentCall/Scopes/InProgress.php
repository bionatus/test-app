<?php

namespace App\Models\AgentCall\Scopes;

use App\Models\AgentCall;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class InProgress implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('status', AgentCall::STATUS_IN_PROGRESS);
    }
}
