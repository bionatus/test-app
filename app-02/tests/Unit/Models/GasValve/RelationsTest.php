<?php

namespace Tests\Unit\Models\GasValve;

use App\Models\GasValve;
use App\Models\Part;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property GasValve $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = GasValve::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
