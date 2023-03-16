<?php

namespace App\Events\AgentCall;

use App\Models\AgentCall;
use App\Models\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Answered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private AgentCall $agentCall;

    public function __construct(AgentCall $agentCall)
    {
        $this->agentCall = $agentCall;
    }

    public function agentCall(): AgentCall
    {
        return $this->agentCall;
    }
}
