<?php

namespace Tests\Unit\Models\OrderLockedData;

use App\Models\Order;
use App\Models\OrderLockedData;
use Tests\Unit\Models\RelationsTestCase;

class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = OrderLockedData::factory()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_an_order()
    {
        $related = $this->instance->order()->first();

        $this->assertInstanceOf(Order::class, $related);
    }
}
