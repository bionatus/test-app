<?php

namespace Tests\Unit\Models\SupplierRequest;

use App\Models\Order;
use Tests\Unit\Models\RelationsTestCase;
use App\Models\MissedOrderRequest;

/**
 * @property MissedOrderRequest $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = MissedOrderRequest::factory()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_an_order()
    {
        $related = $this->instance->order()->first();
        $this->assertInstanceOf(Order::class, $related);
    }
}
