<?php

namespace Tests\Unit\Models\ItemOrderSnap;

use App\Models\Item;
use App\Models\ItemOrderSnap;
use App\Models\OrderSnap;
use App\Models\Replacement;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property ItemOrderSnap $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = ItemOrderSnap::factory()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_an_order_snap()
    {
        $related = $this->instance->orderSnap()->first();

        $this->assertInstanceOf(OrderSnap::class, $related);
    }

    /** @test */
    public function it_belongs_to_an_item()
    {
        $related = $this->instance->item()->first();

        $this->assertInstanceOf(Item::class, $related);
    }

    /** @test */
    public function it_belongs_to_an_order()
    {
        $related = $this->instance->orderSnap()->first();

        $this->assertInstanceOf(OrderSnap::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_replacement()
    {
        $itemOrderSnap = ItemOrderSnap::factory()->usingReplacement(Replacement::factory()->create())->createQuietly();

        $related = $itemOrderSnap->replacement()->first();

        $this->assertInstanceOf(Replacement::class, $related);
    }
}
