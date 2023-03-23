<?php

namespace Tests\Unit\Models\AgentCall\Scopes;

use App\Models\AgentCall;
use App\Models\AgentCall\Scopes\ByAgentId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByAgentIdTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_agent_id()
    {
        AgentCall::factory()->create();
        $agentCall = AgentCall::factory()->create();

        $foundAgentCall = AgentCall::scoped(new ByAgentId($agentCall->agent_id))->first();

        $this->assertEquals($agentCall->getKey(), $foundAgentCall->getKey());
    }
}
