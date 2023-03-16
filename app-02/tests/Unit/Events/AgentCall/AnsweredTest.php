<?php

namespace Tests\Unit\Events\AgentCall;

use App\Events\AgentCall\Answered;
use App\Listeners\AgentCall\SendAgentAnsweredNotification;
use App\Listeners\AgentCall\SendTechEngagedNotification;
use App\Models\AgentCall;
use Tests\TestCase;

class AnsweredTest extends TestCase
{
    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(Answered::class, [
            SendAgentAnsweredNotification::class,
            SendTechEngagedNotification::class,
        ]);
    }

    /** @test */
    public function it_returns_its_agent_call()
    {
        $agentCall = new AgentCall();

        $event = new Answered($agentCall);

        $this->assertSame($agentCall, $event->agentCall());
    }
}
