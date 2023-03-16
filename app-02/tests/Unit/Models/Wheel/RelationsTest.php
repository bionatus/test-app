<?php

namespace Tests\Unit\Models\Wheel;

use App\Models\Part;
use App\Models\Wheel;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Wheel $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Wheel::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
