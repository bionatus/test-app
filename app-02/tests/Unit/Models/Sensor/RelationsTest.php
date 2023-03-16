<?php

namespace Tests\Unit\Models\Sensor;

use App\Models\Part;
use App\Models\Sensor;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Sensor $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Sensor::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
