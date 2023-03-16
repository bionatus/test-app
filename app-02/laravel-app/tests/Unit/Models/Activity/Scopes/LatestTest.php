<?php

namespace Tests\Unit\Models\Activity\Scopes;

use App\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LatestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_newest_creation_and_id()
    {
        $activities = Activity::factory()->count(3)->create();

        $latest = $activities->reverse()->values();

        $this->assertEquals($latest->toArray(), Activity::scoped(new Activity\Scopes\Latest())->get()->toArray());
    }
}
