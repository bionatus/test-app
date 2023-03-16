<?php

namespace Tests\Unit\Models\Agent\Scopes;

use App\Models\Agent;
use App\Models\Agent\Scopes\Oldest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OldestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sorts_by_oldest_id()
    {
        $agents = Agent::factory()->count(3)->create()->fresh();

        $this->assertEquals($agents->toArray(), Agent::scoped(new Oldest())->get()->toArray());
    }
}
