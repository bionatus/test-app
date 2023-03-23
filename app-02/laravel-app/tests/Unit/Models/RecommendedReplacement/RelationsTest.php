<?php

namespace Tests\Unit\Models\RecommendedReplacement;

use App\Models\Part;
use App\Models\RecommendedReplacement;
use App\Models\Supplier;
use Tests\Unit\Models\RelationsTestCase;

class RelationsTest extends RelationsTestCase
{
    protected function setUp():void
    {
        parent::setUp();

        $this->instance = RecommendedReplacement::factory()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_a_supplier()
    {
        $related = $this->instance->supplier;

        $this->assertInstanceOf(Supplier::class, $related);
    }

    /** @test */
    public function it_belonggs_to_a_part()
    {
        $related = $this->instance->part;

        $this->assertInstanceOf(Part::class, $related);
    }
}
