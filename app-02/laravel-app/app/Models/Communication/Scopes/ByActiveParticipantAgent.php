<?php

namespace App\Models\Communication\Scopes;

use App\Models\Agent;
use App\Models\AgentCall\Scopes\Completed;
use App\Models\Scopes\ByAgent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByActiveParticipantAgent implements Scope
{
    private Agent $agent;

    public function __construct(Agent $agent)
    {
        $this->agent = $agent;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas('agentCalls', function(Builder $builder) {
            $builder->scoped(new ByAgent($this->agent));
            $builder->scoped(new Completed());
        });
    }
}
