<?php

namespace Tests\Unit\Models\SharedOrder;

use App\Models\Order;
use App\Models\SharedOrder;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property SharedOrder $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = SharedOrder::factory()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_order()
    {
        $related = $this->instance->order()->first();

        $this->assertInstanceOf(Order::class, $related);
    }
}
