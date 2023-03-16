<?php

namespace App\Models\Scopes;

use App\Models\Agent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ByAgent implements Scope
{
    private Agent $agent;

    public function __construct(Agent $agent)
    {
        $this->agent = $agent;
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('agent_id', $this->agent->getKey());
    }
}
