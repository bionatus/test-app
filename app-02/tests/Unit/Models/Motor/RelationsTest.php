<?php

namespace Tests\Unit\Models\Motor;

use App\Models\Motor;
use App\Models\Part;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Motor $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Motor::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
