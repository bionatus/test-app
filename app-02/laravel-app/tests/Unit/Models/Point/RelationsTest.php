<?php

namespace Tests\Unit\Models\Point;

use App\Models\Order;
use App\Models\Point;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Point $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Point::factory()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_has_an_object()
    {
        $objectOrder = Point::factory()->createQuietly();
        $object      = $objectOrder->object()->first();

        $this->assertInstanceOf(Order::class, $object);
    }
}
