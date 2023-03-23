<?php

namespace Tests\Unit\Models\TemperatureControl;

use App\Models\Part;
use App\Models\TemperatureControl;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property TemperatureControl $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = TemperatureControl::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
