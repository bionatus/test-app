<?php

namespace Tests\Unit\Models\Layout\Scopes;

use App\Models\Layout;
use App\Models\Layout\Scopes\Highest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HighestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_descending_by_version()
    {
        $layouts  = Layout::factory()->count(10)->create();
        $expected = $layouts->sortByDesc('version')->pluck('version', Layout::routeKeyName());

        $actual = Layout::scoped(new Highest())->get()->pluck('version', Layout::routeKeyName());;

        $this->assertSame($expected->toArray(), $actual->toArray());
    }
}
