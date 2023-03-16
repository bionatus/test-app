<?php

namespace Tests\Unit\Models\ControlBoard;

use App\Models\ControlBoard;
use App\Models\Part;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property ControlBoard $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = ControlBoard::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
