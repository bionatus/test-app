<?php

namespace Tests\Unit\Models;

use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\SingleReplacement;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Str;

class ItemOrderTest extends ModelTestCase
{
    use RefreshDatabase;
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(ItemOrder::tableName(), [
            'id',
            'uuid',
            'item_id',
            'order_id',
            'replacement_id',
            'quantity',
            'quantity_requested',
            'price',
            'supply_detail',
            'custom_detail',
            'generic_part_description',
            'status',
            'initial_request',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_uses_uuid()
    {
        $itemOrder = ItemOrder::factory()->createQuietly(['uuid' => Str::uuid()->toString()]);

        $this->assertEquals($itemOrder->uuid, $itemOrder->getRouteKey());
    }

    /** @test */
    public function it_fills_uuid_on_creation()
    {
        $itemOrder = ItemOrder::factory()->make(['uuid' => null]);
        $itemOrder->save();

        $this->assertNotNull($itemOrder->uuid);
    }

    /** @test */
    public function it_knows_if_is_pending()
    {
        $itemOrderNotPending = ItemOrder::factory()->createQuietly(['status' => ItemOrder::STATUS_AVAILABLE]);
        $itemOrderPending    = ItemOrder::factory()->createQuietly(['status' => ItemOrder::STATUS_PENDING]);

        $this->assertFalse($itemOrderNotPending->isPending());
        $this->assertTrue($itemOrderPending->isPending());
    }

    public function it_knows_when_it_does_not_have_any_replacement()
    {
        $itemOrder = ItemOrder::factory()->createQuietly();

        $this->assertFalse($itemOrder->hasAnyReplacement());
    }

    /** @test */
    public function it_knows_when_it_has_replacement()
    {
        $singleReplacement = SingleReplacement::factory()->create();
        $itemOrder         = ItemOrder::factory()->usingReplacement($singleReplacement->replacement)->createQuietly();

        $this->assertTrue($itemOrder->hasAnyReplacement());
    }

    /** @test */
    public function it_knows_when_it_has_replacement_generic()
    {
        $itemOrder = ItemOrder::factory()->createQuietly(['generic_part_description' => 'Lorem Ipsum Description']);

        $this->assertTrue($itemOrder->hasAnyReplacement());
    }

    /** @test */
    public function it_knows_if_is_removed()
    {
        $supplier            = Supplier::factory()->createQuietly();
        $order               = Order::factory()->usingSupplier($supplier)->create();
        $itemOrderRemoved    = ItemOrder::factory()->usingOrder($order)->removed()->create();
        $itemOrderNotRemoved = ItemOrder::factory()->usingOrder($order)->pending()->create();

        $this->assertFalse($itemOrderNotRemoved->isRemoved());
        $this->assertTrue($itemOrderRemoved->isRemoved());
    }

    /** @test */
    public function it_will_not_log_activity_on_create()
    {
        $supplier  = Supplier::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($supplier)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->create();

        $this->assertEquals(0, $itemOrder->activities->count());
    }

    /** @test */
    public function it_will_log_activity_on_update()
    {
        $supplier  = Supplier::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($supplier)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->create();
        $itemOrder->update(['status' => ItemOrder::STATUS_AVAILABLE]);

        $this->assertEquals(1, $itemOrder->activities->count());
        $this->assertDatabaseHas('activity_log', [
            'log_name'                       => 'order_log',
            'description'                    => 'item_order.updated',
            'subject_type'                   => 'item_order',
            'subject_id'                     => $itemOrder->getKey(),
            'resource'                       => 'item_order',
            'event'                          => 'updated',
            'properties->old->status'        => 'pending',
            'properties->attributes->status' => 'available',
        ]);
    }

    /** @test */
    public function it_updates_the_updated_at_field_from_order_when_creating_an_item_order()
    {
        $updated_at = Carbon::now()->subDays(4);
        $order      = Order::factory()->create(['updated_at' => $updated_at]);
        $itemOrder  = ItemOrder::factory()->usingOrder($order)->create();
        $order->refresh();

        $this->assertEquals(Carbon::now()->startOfSecond(), $itemOrder->updated_at);
        $this->assertEquals($order->updated_at, $itemOrder->updated_at);
    }

    /** @test */
    public function it_updates_the_updated_at_field_from_order_when_updating_an_item_order()
    {
        $updated_at        = Carbon::now()->subDays(4);
        $order             = Order::factory()->create();
        $itemOrder         = ItemOrder::factory()->usingOrder($order)->create(['updated_at' => $updated_at]);
        $order->updated_at = $updated_at;
        $order->save();

        $now                   = Carbon::now()->startOfSecond();
        $itemOrder->updated_at = $now;
        $itemOrder->save();
        $order->refresh();

        $this->assertEquals($now, $order->updated_at);
        $this->assertEquals($now, $itemOrder->updated_at);
    }
}
