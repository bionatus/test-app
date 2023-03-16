<?php

namespace Tests\Unit\Models\Part\Scopes;

use App\Models\Part;
use App\Models\Part\Scopes\ByParentRouteKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByParentRouteKeyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_route_key()
    {
        $part = Part::factory()->create();
        Part::factory()->create();

        $filtered = Part::scoped(new ByParentRouteKey($part->item->getRouteKey()))->first();

        $this->assertInstanceOf(Part::class, $filtered);
        $this->assertSame($part->getKey(), $filtered->getKey());
    }
}
