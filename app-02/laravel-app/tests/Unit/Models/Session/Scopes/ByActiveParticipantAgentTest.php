<?php

namespace Tests\Unit\Models\Session\Scopes;

use App\Models\Agent;
use App\Models\AgentCall;
use App\Models\Call;
use App\Models\Communication;
use App\Models\Session;
use App\Models\Session\Scopes\ByActiveParticipantAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByActiveParticipantAgentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_active_participant_agent()
    {
        $agent = Agent::factory()->create();

        AgentCall::factory()->usingAgent($agent)->completed()->count(2)->create();
        $agentCall = AgentCall::factory()->completed()->usingAgent($agent)->create();
        AgentCall::factory()->usingAgent($agent)->usingCall($agentCall->call)->create();
        $session       = $agentCall->call->communication->session;
        $communication = Communication::factory()->usingSession($session)->create();
        $call          = Call::factory()->usingCommunication($communication)->create();
        AgentCall::factory()->usingAgent($agent)->usingCall($call)->create();

        $sessions = Session::scoped(new ByActiveParticipantAgent($agent))->get();

        $this->assertCount(3, $sessions);
    }
}
