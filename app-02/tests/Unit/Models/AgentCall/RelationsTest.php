<?php

namespace Tests\Unit\Models\AgentCall;

use App\Models\Agent;
use App\Models\AgentCall;
use App\Models\Call;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property AgentCall $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = AgentCall::factory()->create();
    }

    /** @test */
    public function it_belongs_to_an_agent()
    {
        $related = $this->instance->agent()->first();

        $this->assertInstanceOf(Agent::class, $related);
    }

    /** @test */
    public function it_belongs_to_an_call()
    {
        $related = $this->instance->call()->first();

        $this->assertInstanceOf(Call::class, $related);
    }
}
