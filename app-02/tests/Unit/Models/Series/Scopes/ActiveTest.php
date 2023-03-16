<?php

namespace Tests\Unit\Models\Series\Scopes;

use App\Models\Brand;
use App\Models\Series;
use App\Models\Series\Scopes\Active;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_only_active_series()
    {
        $unpublishedBrand = Brand::factory()->unpublished()->create();
        Series::factory()->unpublished()->count(2)->create();
        Series::factory()->published()->count(3)->usingBrand($unpublishedBrand)->create();
        $active = Series::factory()->active()->count(4)->create();

        $found = Series::scoped(new Active())->get();

        $this->assertEquals($active->count(), $found->count());

        $found->each(function(Series $foundItem) use ($active) {
            $this->assertSame($active->shift()->getKey(), $foundItem->getKey());
        });
    }
}
