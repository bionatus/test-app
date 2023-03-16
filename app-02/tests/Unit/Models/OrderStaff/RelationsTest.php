<?php

namespace Tests\Unit\Models\OrderStaff;

use App\Models\Order;
use App\Models\OrderStaff;
use App\Models\Staff;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property OrderStaff $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = OrderStaff::factory()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_a_order()
    {
        $related = $this->instance->order()->first();

        $this->assertInstanceOf(Order::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_staff()
    {
        $related = $this->instance->staff()->first();

        $this->assertInstanceOf(Staff::class, $related);
    }
}
