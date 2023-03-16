<?php

namespace Tests\Unit\Models\Substatus;

use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Status;
use App\Models\Substatus;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Substatus $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Substatus::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_status()
    {
        $related = $this->instance->status()->first();

        $this->assertInstanceOf(Status::class, $related);
    }

    /** @test */
    public function it_has_order_statuses()
    {
        OrderSubstatus::factory()->usingSubstatus($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->orderSubstatuses()->get();

        $this->assertCorrectRelation($related, OrderSubstatus::class);
    }

    /** @test */
    public function it_has_orders()
    {
        OrderSubstatus::factory()->usingSubstatus($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->orders()->get();

        $this->assertCorrectRelation($related, Order::class);
    }
}
