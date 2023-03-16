<?php

namespace Tests\Unit\Models\HardStartKit;

use App\Models\HardStartKit;
use App\Models\Part;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property HardStartKit $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = HardStartKit::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
