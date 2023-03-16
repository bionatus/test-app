<?php

namespace Tests\Unit\Models\FanBlade;

use App\Models\FanBlade;
use App\Models\Part;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property FanBlade $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = FanBlade::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
