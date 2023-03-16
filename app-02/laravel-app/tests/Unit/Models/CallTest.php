<?php

namespace Tests\Unit\Models;

use App\Models\AgentCall;
use App\Models\Call;
use Mockery;

class CallTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Call::tableName(), [
            'id',
            'status',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_completes_the_call()
    {
        $mock = Mockery::mock(Call::class);
        $mock->makePartial();
        $mock->shouldReceive('setAttribute')->withArgs(['status', Call::STATUS_COMPLETED])->once();
        $mock->shouldReceive('save')->withNoArgs()->once();
        $mock->shouldReceive('freeAgents')->withNoArgs()->once();
        $call = $mock->complete();

        $this->assertEquals($mock, $call);
    }

    /** @test */
    public function it_frees_agents()
    {
        $call                 = Call::factory()->inProgress()->create();
        $inProgressAgentCalls = AgentCall::factory()->usingCall($call)->count(3)->inProgress()->create();
        $ringingAgentCalls    = AgentCall::factory()->usingCall($call)->count(3)->ringing()->create();

        $call->freeAgents();
        $inProgressAgentCalls->each(function(AgentCall $agentCall) {
            $this->assertEquals(AgentCall::STATUS_COMPLETED, $agentCall->fresh()->status);
        });

        $ringingAgentCalls->each(function(AgentCall $agentCall) {
            $this->assertEquals(AgentCall::STATUS_DROPPED, $agentCall->fresh()->status);
        });
    }
}
