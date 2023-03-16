<?php

namespace Tests\Unit\Models\ItemOrder;

use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Replacement;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property ItemOrder $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = ItemOrder::factory()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_an_item()
    {
        $related = $this->instance->item()->first();

        $this->assertInstanceOf(Item::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_order()
    {
        $related = $this->instance->order()->first();

        $this->assertInstanceOf(Order::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_replacement()
    {
        $itemOrder = ItemOrder::factory()->usingReplacement(Replacement::factory()->create())->createQuietly();

        $related = $itemOrder->replacement()->first();

        $this->assertInstanceOf(Replacement::class, $related);
    }
}
