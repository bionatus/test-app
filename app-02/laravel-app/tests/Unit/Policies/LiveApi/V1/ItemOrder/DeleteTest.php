<?php

namespace Tests\Unit\Policies\LiveApi\V1\ItemOrder;

use App\Models\CustomItem;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Staff;
use App\Models\Supplier;
use App\Policies\LiveApi\V1\ItemOrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_disallows_another_processor_to_delete_a_custom_item_from_an_order()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $supplierOrder = Supplier::factory()->createQuietly();
        $notProcessor  = Staff::factory()->usingSupplier($supplier)->create();

        $order     = Order::factory()->usingSupplier($supplierOrder)->approved()->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->create();
        CustomItem::factory()->usingItem($itemOrder->item)->usingSupplier($supplierOrder)->create();

        $policy = new ItemOrderPolicy();

        $this->assertFalse($policy->delete($notProcessor, $itemOrder));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_disallows_the_processor_to_delete_an_item_when_not_is_custom_item(
        string $itemType,
        bool $expectedResult
    ) {
        $supplier  = Supplier::factory()->createQuietly();
        $processor = Staff::factory()->usingSupplier($supplier)->create();
        $order     = Order::factory()->usingSupplier($supplier)->create();
        $item      = Item::factory()->create(['type' => $itemType]);
        $itemOrder = ItemOrder::factory()->usingItem($item)->usingOrder($order)->create();
        
        if ($itemType == Item::TYPE_CUSTOM_ITEM) {
            CustomItem::factory()->usingItem($item)->usingSupplier($supplier)->create();
        }

        $policy = new ItemOrderPolicy();

        $this->assertEquals($expectedResult, $policy->delete($processor, $itemOrder));
    }

    public function dataProvider(): array
    {
        return [
            [Item::TYPE_SUPPLY, false],
            [Item::TYPE_PART, false],
            [Item::TYPE_CUSTOM_ITEM, true],
        ];
    }
}
