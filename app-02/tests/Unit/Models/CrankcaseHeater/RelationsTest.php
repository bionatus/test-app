<?php

namespace Tests\Unit\Models\CrankcaseHeater;

use App\Models\CrankcaseHeater;
use App\Models\Part;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property CrankcaseHeater $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = CrankcaseHeater::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
