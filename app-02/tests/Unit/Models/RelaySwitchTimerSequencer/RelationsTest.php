<?php

namespace Tests\Unit\Models\RelaySwitchTimerSequencer;

use App\Models\Part;
use App\Models\RelaySwitchTimerSequencer;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property RelaySwitchTimerSequencer $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = RelaySwitchTimerSequencer::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
