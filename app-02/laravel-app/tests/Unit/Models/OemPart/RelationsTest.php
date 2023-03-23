<?php

namespace Tests\Unit\Models\OemPart;

use App\Models\Oem;
use App\Models\OemPart;
use App\Models\Part;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property OemPart $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = OemPart::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }

    /** @test */
    public function it_belongs_to_an_oem()
    {
        $related = $this->instance->oem()->first();

        $this->assertInstanceOf(Oem::class, $related);
    }
}
