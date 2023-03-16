<?php

namespace Tests\Unit\Events\AgentCall;

use App\Events\AgentCall\Ringing;
use App\Listeners\AgentCall\SendTechCallingNotification;
use App\Models\AgentCall;
use Tests\TestCase;

class RingingTest extends TestCase
{
    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(Ringing::class, [
            SendTechCallingNotification::class,
        ]);
    }

    /** @test */
    public function it_returns_its_agent_call()
    {
        $agentCall = new AgentCall();

        $event = new Ringing($agentCall);

        $this->assertSame($agentCall, $event->agentCall());
    }
}
