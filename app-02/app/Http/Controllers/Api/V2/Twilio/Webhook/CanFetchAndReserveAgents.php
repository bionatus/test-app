<?php

namespace App\Http\Controllers\Api\V2\Twilio\Webhook;

use App;
use App\Models\Agent;
use App\Models\Agent\Scopes\Available;
use App\Models\Agent\Scopes\NotEngagedInCall;
use App\Models\AgentCall;
use App\Models\Call;
use App\Models\Scopes\ExceptKey;
use App\Models\Scopes\ExceptKeys;
use App\Models\User;

trait CanFetchAndReserveAgents
{
    private function fetchAndReserveAgent(Call $call, User $tech): ?Agent
    {
        if (!($agent = $this->fetchNextAgent($call, $tech))) {
            return null;
        }

        $this->reserveAgent($call, $agent);

        return $agent;
    }

    private function fetchNextAgent(Call $call, User $tech): ?Agent
    {
        return Agent::scoped(new ExceptKey($tech->getKey()))
            ->scoped(new ExceptKeys($call->agents->modelKeys()))
            ->scoped(new NotEngagedInCall())
            ->scoped(new Available())
            ->inRandomOrder()
            ->first();
    }

    private function reserveAgent(Call $call, Agent $agent): void
    {
        $call->agentCalls()->create([
            'agent_id' => $agent->getKey(),
            'status'   => AgentCall::STATUS_IN_PROGRESS,
        ]);
    }
}
