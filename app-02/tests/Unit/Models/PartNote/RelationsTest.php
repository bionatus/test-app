<?php

namespace Tests\Unit\Models\PartNote;

use App\Models\Part;
use App\Models\PartNote;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property PartNote $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = PartNote::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
