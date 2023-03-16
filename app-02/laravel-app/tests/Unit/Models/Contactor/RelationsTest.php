<?php

namespace Tests\Unit\Models\Contactor;

use App\Models\Contactor;
use App\Models\Part;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Contactor $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Contactor::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
