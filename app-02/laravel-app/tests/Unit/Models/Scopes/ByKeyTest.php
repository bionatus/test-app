<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Agent;
use App\Models\Scopes\ByKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByKeyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_route_key()
    {
        $agent = Agent::factory()->create();

        $filtered = Agent::scoped(new ByKey($agent->getKey()))->first();

        $this->assertInstanceOf(Agent::class, $filtered);
        $this->assertSame($agent->getKey(), $filtered->getKey());
    }
}
