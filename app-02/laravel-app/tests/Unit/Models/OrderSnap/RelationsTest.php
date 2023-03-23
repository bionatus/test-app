<?php

namespace Tests\Unit\Models\OrderSnap;

use App\Models\Item;
use App\Models\ItemOrderSnap;
use App\Models\Oem;
use App\Models\Order;
use App\Models\OrderSnap;
use App\Models\Supplier;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property OrderSnap $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = OrderSnap::factory()->createQuietly();
    }

    /** @test */
    public function it_has_items()
    {
        ItemOrderSnap::factory()->usingOrderSnap($this->instance)->count(10)->create();

        $related = $this->instance->items()->get();

        $this->assertCorrectRelation($related, Item::class);
    }

    /** @test */
    public function it_has_item_order_snaps()
    {
        ItemOrderSnap::factory()->usingOrderSnap($this->instance)->count(10)->create();

        $related = $this->instance->itemOrderSnaps()->get();

        $this->assertCorrectRelation($related, ItemOrderSnap::class);
    }

    /** @test */
    public function it_belongs_to_an_order()
    {
        $supplier = Supplier::factory()->createQuietly();
        $this->instance->order()->associate(Order::factory()->usingSupplier($supplier)->create());

        $related = $this->instance->order()->first();

        $this->assertInstanceOf(Order::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $this->instance->user()->associate(User::factory()->create());

        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_supplier()
    {
        $this->instance->supplier()->associate(Supplier::factory()->createQuietly());

        $related = $this->instance->supplier()->first();

        $this->assertInstanceOf(Supplier::class, $related);
    }

    /** @test */
    public function it_belongs_to_an_oem()
    {
        $this->instance->oem()->associate(Oem::factory()->create());

        $related = $this->instance->oem()->first();

        $this->assertInstanceOf(Oem::class, $related);
    }
}
