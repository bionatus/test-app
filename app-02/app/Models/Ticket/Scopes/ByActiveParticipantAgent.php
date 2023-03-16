<?php

namespace App\Models\Ticket\Scopes;

use App\Models\Agent;
use App\Models\Session\Scopes\ByActiveParticipantAgent as SessionsByActiveParticipantAgent;
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
        $builder->whereHas('sessions', function(Builder $builder) {
            $builder->scoped(new SessionsByActiveParticipantAgent($this->agent));
        });
    }
}
