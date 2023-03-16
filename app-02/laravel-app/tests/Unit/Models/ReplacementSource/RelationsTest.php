<?php

namespace Tests\Unit\Models\ReplacementSource;

use App\Models\Replacement;
use App\Models\ReplacementSource;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property ReplacementSource $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = ReplacementSource::factory()->create();
    }

    /** @test */
    public function it_is_a_replacement()
    {
        $related = $this->instance->replacement()->first();

        $this->assertInstanceOf(Replacement::class, $related);
    }
}
