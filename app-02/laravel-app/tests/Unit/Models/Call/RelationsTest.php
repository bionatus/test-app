<?php

namespace Tests\Unit\Models\Call;

use App\Models\Agent;
use App\Models\AgentCall;
use App\Models\Call;
use App\Models\Communication;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Call $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Call::factory()->create();
    }

    /** @test */
    public function it_is_a_communication()
    {
        $related = $this->instance->communication()->first();

        $this->assertInstanceOf(Communication::class, $related);
    }

    /** @test */
    public function it_has_agents()
    {
        AgentCall::factory()->usingCall($this->instance)->count(10)->create();

        $related = $this->instance->agents()->get();

        $this->assertCorrectRelation($related, Agent::class);
    }

    /** @test */
    public function it_has_agent_calls()
    {
        AgentCall::factory()->usingCall($this->instance)->count(10)->create();

        $related = $this->instance->agentCalls()->get();

        $this->assertCorrectRelation($related, AgentCall::class);
    }
}
