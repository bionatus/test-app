<?php

namespace Tests\Unit\Models\Level\Scopes;

use App\Models\Level;
use App\Models\Level\Scopes\ByLevelRange;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByLevelRangeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filter_by_level_range_based_on_earned_points()
    {
        Level::factory()->create([
            'slug' => $level0 = 'level-0',
            'from' => 0,
            'to'   => 999,
        ]);
        Level::factory()->create([
            'slug' => $level1 = 'level-1',
            'from' => 1000,
            'to'   => null,
        ]);

        $firstTotalEarnedPoints = 10;
        $filteredLevel          = Level::scoped(new ByLevelRange($firstTotalEarnedPoints))->first();
        $this->assertSame($filteredLevel->slug, $level0);

        $secondTotalEarnedPoints = 1000;
        $filteredLevel           = Level::scoped(new ByLevelRange($secondTotalEarnedPoints))->first();
        $this->assertSame($filteredLevel->slug, $level1);
    }
}
