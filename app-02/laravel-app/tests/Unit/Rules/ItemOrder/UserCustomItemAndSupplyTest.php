<?php

namespace Tests\Unit\Rules\ItemOrder;

use App\Models\CustomItem;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\User;
use App\Rules\ItemOrder\UserCustomItemAndSupply;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCustomItemAndSupplyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->login($this->user);
    }

    /** @test */
    public function it_returns_a_custom_message()
    {
        $rule = new UserCustomItemAndSupply();

        $this->assertSame('The item should be type supply or custom item added by the technician.', $rule->message());
    }

    /** @test */
    public function it_fails_if_there_is_not_valid_item_order()
    {
        $rule = new UserCustomItemAndSupply();

        $this->assertFalse($rule->passes('attribute', 'invalid'));
    }

    /** @test */
    public function it_passes_with_a_valid_item_order_of_supply_type()
    {
        $supplier  = Supplier::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($supplier)->create();
        $item      = Item::factory()->supply()->create();
        $itemOrder = ItemOrder::factory()->usingItem($item)->usingOrder($order)->create();

        $rule = new UserCustomItemAndSupply();

        $this->assertTrue($rule->passes('attribute', $itemOrder->getRouteKey()));
    }

    /** @test */
    public function it_passes_with_a_valid_item_order_of_custom_item_type_added_by_tech()
    {
        $supplier        = Supplier::factory()->createQuietly();
        $order           = Order::factory()->usingSupplier($supplier)->create();
        $customItemsUser = CustomItem::factory()->create();
        $itemOrder       = ItemOrder::factory()->usingItem($customItemsUser->item)->usingOrder($order)->create();

        $rule = new UserCustomItemAndSupply();

        $this->assertTrue($rule->passes('attribute', $itemOrder->getRouteKey()));
    }
}
