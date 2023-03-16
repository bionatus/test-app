<?php

namespace App\Rules\AgentCall;

use App\Models\AgentCall;
use App\Models\AgentCall\Scopes\ByAgentId;
use App\Rules\Call;
use Illuminate\Contracts\Validation\Rule;
use Lang;
use Str;

class Exists implements Rule
{
    private Call\Exists $callExists;
    private AgentCall   $agentCall;

    public function __construct(Call\Exists $callExists)
    {
        $this->callExists = $callExists;
        $this->agentCall  = new AgentCall();
    }

    public function passes($attribute, $value)
    {
        $call = $this->callExists->call();
        if (!$call->exists) {
            return false;
        }

        $agentId = Str::substr($value, strlen('client:'), strlen($value));
        /** @var AgentCall $agentCall */
        if ($agentCall = $call->agentCalls()->scoped(new ByAgentId($agentId))->first()) {
            $this->agentCall = $agentCall;

            return true;
        }

        return false;
    }

    public function message()
    {
        return Lang::get('validation.exists');
    }

    public function agentCall(): AgentCall
    {
        return $this->agentCall;
    }
}
