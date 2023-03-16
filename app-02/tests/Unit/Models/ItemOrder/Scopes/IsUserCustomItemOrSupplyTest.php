<?php

namespace Tests\Unit\Models\ItemOrder\Scopes;

use App\Models\CustomItem;
use App\Models\ItemOrder;
use App\Models\ItemOrder\Scopes\IsUserCustomItemOrSupply;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\Supply;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IsUserCustomItemOrSupplyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_user_custom_item()
    {
        $supplier   = Supplier::factory()->createQuietly();
        $order      = Order::factory()->usingSupplier($supplier)->create();

        $customItemsUser = CustomItem::factory()->count(4)->create();
        $customItemsUser->each(function(CustomItem $customItem) use ($order) {
            ItemOrder::factory()->usingOrder($order)->usingItem($customItem->item)->create();
        });

        Supply::factory()->count(3)->create();
        $supplies = Supply::factory()->count(5)->create();
        $supplies->each(function(Supply $supply) use ($order) {
            ItemOrder::factory()->usingOrder($order)->usingItem($supply->item)->create();
        });

        $customItemsSuppliers = CustomItem::factory()->usingSupplier($supplier)->count(10)->create();
        $customItemsSuppliers->each(function(CustomItem $customItem) use ($order) {
            ItemOrder::factory()->usingOrder($order)->usingItem($customItem->item)->create();
        });

        $filtered = ItemOrder::query()->scoped(new IsUserCustomItemOrSupply())->get();
        $this->assertCount(9, $filtered);

        $expectedItems = $customItemsUser->concat($supplies);
        $this->assertEqualsCanonicalizing($expectedItems->pluck('item'), $filtered->pluck('item'));
    }
}
