<?php

namespace Tests\Unit\Models\Supply;

use App\Models\CartSupplyCounter;
use App\Models\Item;
use App\Models\Supply;
use App\Models\SupplyCategory;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Supply $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Supply::factory()->create();
    }

    /** @test */
    public function it_is_an_item()
    {
        $related = $this->instance->item()->first();

        $this->assertInstanceOf(Item::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_supply_category()
    {
        $related = $this->instance->supplyCategory()->first();

        $this->assertInstanceOf(SupplyCategory::class, $related);
    }

    /** @test */
    public function it_has_cart_supply_counters()
    {
        CartSupplyCounter::factory()->usingSupply($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->cartSupplyCounters()->get();

        $this->assertCorrectRelation($related, CartSupplyCounter::class);
    }
}
