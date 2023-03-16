<?php

namespace Tests\Unit\Observers;

use App\Models\Agent;
use App\Observers\AgentObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $agent = Agent::factory()->make(['uuid' => null]);

        $observer = new AgentObserver();

        $observer->creating($agent);

        $this->assertNotNull($agent->uuid);
    }
}
