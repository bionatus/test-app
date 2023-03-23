<?php

namespace Tests\Unit\Models\Other;

use App\Models\Other;
use App\Models\Part;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Other $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Other::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
