<?php

namespace Tests\Unit\Models\ReplacementNote;

use App\Models\Replacement;
use App\Models\ReplacementNote;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property ReplacementNote $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = ReplacementNote::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_replacement()
    {
        $related = $this->instance->replacement()->first();

        $this->assertInstanceOf(Replacement::class, $related);
    }
}
