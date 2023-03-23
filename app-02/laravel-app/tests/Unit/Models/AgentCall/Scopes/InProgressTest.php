<?php

namespace Tests\Unit\Models\AgentCall\Scopes;

use App\Models\AgentCall;
use App\Models\AgentCall\Scopes\InProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InProgressTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_ringing()
    {
        AgentCall::factory()->invalid()->count(1)->create();
        AgentCall::factory()->ringing()->count(2)->create();
        AgentCall::factory()->inProgress()->count(3)->create();
        AgentCall::factory()->completed()->count(4)->create();

        $agentCalls = AgentCall::scoped(new InProgress())->get();

        $this->assertCount(3, $agentCalls);
    }
}
