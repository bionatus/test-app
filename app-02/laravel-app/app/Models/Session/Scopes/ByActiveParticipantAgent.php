<?php

namespace App\Models\Session\Scopes;

use App\Models\Agent;
use App\Models\Communication\Scopes\ByActiveParticipantAgent as CommunicationsByActiveParticipantAgent;
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
        $builder->whereHas('communications', function(Builder $builder) {
            $builder->scoped(new CommunicationsByActiveParticipantAgent($this->agent));
        });
    }
}
