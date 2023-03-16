<?php

namespace Tests\Unit\Models\AgentCall\Scopes;

use App\Models\AgentCall;
use App\Models\AgentCall\Scopes\Ringing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RingingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_ringing()
    {
        AgentCall::factory()->invalid()->count(1)->create();
        AgentCall::factory()->ringing()->count(2)->create();
        AgentCall::factory()->inProgress()->count(3)->create();
        AgentCall::factory()->completed()->count(4)->create();

        $agentCalls = AgentCall::scoped(new Ringing())->get();

        $this->assertCount(2, $agentCalls);
    }
}
