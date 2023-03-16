<?php

namespace Tests\Unit\Models\AgentCall\Scopes;

use App\Models\AgentCall;
use App\Models\AgentCall\Scopes\Completed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompletedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_completed()
    {
        AgentCall::factory()->invalid()->count(1)->create();
        AgentCall::factory()->ringing()->count(2)->create();
        AgentCall::factory()->inProgress()->count(3)->create();
        AgentCall::factory()->completed()->count(4)->create();

        $agentCalls = AgentCall::scoped(new Completed())->get();

        $this->assertCount(4, $agentCalls);
    }
}
