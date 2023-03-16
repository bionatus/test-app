<?php

namespace Tests\Unit\Models\XoxoRedemption;

use App\Models\Point;
use App\Models\XoxoRedemption;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property XoxoRedemption $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = XoxoRedemption::factory()->create();
    }

    /** @test */
    public function it_has_a_point()
    {
        Point::factory()->usingXoxoRedemption($this->instance)->create();

        $related = $this->instance->point;

        $this->assertInstanceOf(Point::class, $related);
    }
}
