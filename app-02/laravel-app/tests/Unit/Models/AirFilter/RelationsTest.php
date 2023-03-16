<?php

namespace Tests\Unit\Models\AirFilter;

use App\Models\AirFilter;
use App\Models\Part;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property AirFilter $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = AirFilter::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
