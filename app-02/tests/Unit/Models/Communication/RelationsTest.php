<?php

namespace Tests\Unit\Models\Communication;

use App\Models\AgentCall;
use App\Models\Call;
use App\Models\Communication;
use App\Models\CommunicationLog;
use App\Models\Session;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Communication $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Communication::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_session()
    {
        $related = $this->instance->session()->first();

        $this->assertInstanceOf(Session::class, $related);
    }

    /** @test */
    public function it_is_a_call()
    {
        $communication = Communication::factory()->call()->create();

        Call::factory()->usingCommunication($communication)->create();

        $related = $communication->call()->first();

        $this->assertInstanceOf(Call::class, $related);
    }

    /** @test */
    public function it_has_agent_calls()
    {
        $call = Call::factory()->usingCommunication($this->instance)->create();

        AgentCall::factory()->usingCall($call)->count(self::COUNT)->create();

        $related = $this->instance->agentCalls()->get();

        $this->assertCorrectRelation($related, AgentCall::class);
    }

    /** @test */
    public function it_has_logs()
    {
        CommunicationLog::factory()->usingCommunication($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->logs()->get();

        $this->assertCorrectRelation($related, CommunicationLog::class);
    }
}
