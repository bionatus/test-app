<?php

namespace Tests\Unit\Models\Compressor;

use App\Models\Compressor;
use App\Models\Part;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Compressor $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Compressor::factory()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
