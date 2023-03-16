<?php

namespace App\Models\AgentCall\Scopes;

use App\Models\AgentCall;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Busy implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereIn('status', [AgentCall::STATUS_RINGING, AgentCall::STATUS_IN_PROGRESS]);
    }
}
