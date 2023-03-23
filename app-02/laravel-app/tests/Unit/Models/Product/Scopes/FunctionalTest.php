<?php

namespace Tests\Unit\Models\Product\Scopes;

use App\Models\Part;
use App\Models\Product\Scopes\Functional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FunctionalTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_types_distinct_than_other()
    {
        foreach (Part::FUNCTIONAL_TYPES as $type) {
            Part::factory()->create(['type' => $type]);
        }
        Part::factory()->create(['type' => Part::TYPE_OTHER]);

        $this->assertEquals(count(Part::FUNCTIONAL_TYPES), Part::scoped(new Functional())->count());
    }
}
