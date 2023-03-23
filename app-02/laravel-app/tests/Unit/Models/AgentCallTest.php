<?php

namespace Tests\Unit\Models;

use App\Models\AgentCall;

class AgentCallTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(AgentCall::tableName(), [
            'id',
            'agent_id',
            'call_id',
            'status',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_knows_if_is_ringing()
    {
        $new        = AgentCall::factory()->make(['status' => null]);
        $ringing    = AgentCall::factory()->ringing()->make();
        $inProgress = AgentCall::factory()->inProgress()->make();
        $completed  = AgentCall::factory()->completed()->make();
        $invalid    = AgentCall::factory()->invalid()->make();

        $this->assertFalse($new->isRinging());
        $this->assertTrue($ringing->isRinging());
        $this->assertFalse($inProgress->isRinging());
        $this->assertFalse($invalid->isRinging());
        $this->assertFalse($completed->isRinging());
    }

    /** @test */
    public function it_knows_if_is_in_progress()
    {
        $new        = AgentCall::factory()->make(['status' => null]);
        $ringing    = AgentCall::factory()->ringing()->make();
        $inProgress = AgentCall::factory()->inProgress()->make();
        $completed  = AgentCall::factory()->completed()->make();
        $invalid    = AgentCall::factory()->invalid()->make();

        $this->assertFalse($new->isInProgress());
        $this->assertFalse($ringing->isInProgress());
        $this->assertTrue($inProgress->isInProgress());
        $this->assertFalse($invalid->isInProgress());
        $this->assertFalse($completed->isInProgress());
    }

    /** @test */
    public function it_knows_if_is_completed()
    {
        $new        = AgentCall::factory()->make(['status' => null]);
        $ringing    = AgentCall::factory()->ringing()->make();
        $inProgress = AgentCall::factory()->inProgress()->make();
        $completed  = AgentCall::factory()->completed()->make();
        $invalid    = AgentCall::factory()->invalid()->make();

        $this->assertFalse($new->isCompleted());
        $this->assertFalse($ringing->isCompleted());
        $this->assertFalse($inProgress->isCompleted());
        $this->assertFalse($invalid->isCompleted());
        $this->assertTrue($completed->isCompleted());
    }
}
