<?php

namespace Tests\Unit\Models\MeteringDevice;

use App\Models\MeteringDevice;
use App\Models\Part;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property MeteringDevice $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = MeteringDevice::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
