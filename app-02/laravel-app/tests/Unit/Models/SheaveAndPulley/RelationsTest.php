<?php

namespace Tests\Unit\Models\SheaveAndPulley;

use App\Models\Part;
use App\Models\SheaveAndPulley;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property SheaveAndPulley $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = SheaveAndPulley::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
