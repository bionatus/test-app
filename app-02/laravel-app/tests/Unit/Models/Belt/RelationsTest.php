<?php

namespace Tests\Unit\Models\Belt;

use App\Models\Belt;
use App\Models\Part;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Belt $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Belt::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
