<?php

namespace App\Models\Agent\Scopes;

use App\Models\AgentCall\Scopes\Busy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class NotEngagedInCall implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereDoesntHave('agentCalls', function(Builder $builder) {
            $builder->scoped(new Busy());
        });
    }
}
