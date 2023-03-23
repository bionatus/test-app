<?php

namespace Tests\Unit\Models\Product;

use App\Models\Product;
use App\Models\Series;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Product $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Product::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_series()
    {
        $related = $this->instance->series()->first();

        $this->assertInstanceOf(Series::class, $related);
    }
}
