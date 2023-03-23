<?php

namespace Tests\Unit\Models\Igniter;

use App\Models\Igniter;
use App\Models\Part;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Igniter $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Igniter::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
