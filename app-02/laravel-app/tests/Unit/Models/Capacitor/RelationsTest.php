<?php

namespace Tests\Unit\Models\Capacitor;

use App\Models\Capacitor;
use App\Models\Part;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Capacitor $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Capacitor::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
