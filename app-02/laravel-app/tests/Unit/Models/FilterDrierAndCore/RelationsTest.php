<?php

namespace Tests\Unit\Models\FilterDrierAndCore;

use App\Models\FilterDrierAndCore;
use App\Models\Part;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property FilterDrierAndCore $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = FilterDrierAndCore::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
