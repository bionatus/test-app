<?php

namespace App\Models\AgentCall\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByAgentId implements Scope
{
    private string $agentId;

    public function __construct(string $agentId)
    {
        $this->agentId = $agentId;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('agent_id', $this->agentId);
    }
}
