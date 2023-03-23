<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Agent;
use App\Models\Scopes\ExceptKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExceptKeyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_excludes_key()
    {
        $agents   = Agent::factory()->count(10)->create();
        $agent    = $agents->first();
        $filtered = Agent::scoped(new ExceptKey($agent->getKey()))->get();
        $ids      = $filtered->pluck(Agent::keyName());

        $this->assertFalse($ids->contains($agent->getKey()));
    }
}
