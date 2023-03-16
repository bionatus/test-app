<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Agent;
use App\Models\Item;
use App\Models\Scopes\ByRouteKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByRouteKeyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_route_key()
    {
        $agent = Agent::factory()->create();

        $filtered = Agent::scoped(new ByRouteKey($agent->getRouteKey()))->first();

        $this->assertInstanceOf(Agent::class, $filtered);
        $this->assertSame($agent->getKey(), $filtered->getKey());
    }

    /** @test */
    public function it_filters_by_route_key_using_item()
    {
        $item = Item::factory()->create();

        $filtered = Item::scoped(new ByRouteKey($item->getRouteKey()))->first();

        $this->assertInstanceOf(Item::class, $filtered);
        $this->assertSame($item->getKey(), $filtered->getKey());
    }
}
