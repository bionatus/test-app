<?php

namespace Tests\Unit\Models\OrderSubstatus;

use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property OrderSubstatus $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = OrderSubstatus::factory()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_a_order()
    {
        $related = $this->instance->order()->first();

        $this->assertInstanceOf(Order::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_substatus()
    {
        $related = $this->instance->substatus()->first();

        $this->assertInstanceOf(Substatus::class, $related);
    }
}
