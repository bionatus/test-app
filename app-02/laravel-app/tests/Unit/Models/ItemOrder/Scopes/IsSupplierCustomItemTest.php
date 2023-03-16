<?php

namespace Tests\Unit\Models\ItemOrder\Scopes;

use App\Models\CustomItem;
use App\Models\ItemOrder;
use App\Models\ItemOrder\Scopes\IsSupplierCustomItem;
use App\Models\Order;
use App\Models\Staff;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IsSupplierCustomItemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_supplier_custom_item()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();

        $customItemsUser = CustomItem::factory()->count(4)->create();
        $customItemsUser->each(function(CustomItem $customItem) use ($order) {
            ItemOrder::factory()->usingOrder($order)->usingItem($customItem->item)->create();
        });

        $customItemsSuppliers = CustomItem::factory()->usingSupplier($supplier)->count(10)->create();
        $customItemsSuppliers->each(function(CustomItem $customItem) use ($order) {
            ItemOrder::factory()->usingOrder($order)->usingItem($customItem->item)->create();
        });

        $filtered = ItemOrder::query()->scoped(new IsSupplierCustomItem())->get();
        $this->assertCount(10, $filtered);

        $this->assertEqualsCanonicalizing($customItemsSuppliers->pluck('item'), $filtered->pluck('item'));
    }
}
