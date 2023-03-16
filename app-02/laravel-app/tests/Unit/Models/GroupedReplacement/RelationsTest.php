<?php

namespace Tests\Unit\Models\GroupedReplacement;

use App\Models\Part;
use App\Models\Replacement;
use App\Models\GroupedReplacement;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property GroupedReplacement $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = GroupedReplacement::factory()->create();
    }

    /** @test */
    public function it_is_a_replacement()
    {
        $related = $this->instance->replacement()->first();

        $this->assertInstanceOf(Replacement::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_part()
    {
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }
}
