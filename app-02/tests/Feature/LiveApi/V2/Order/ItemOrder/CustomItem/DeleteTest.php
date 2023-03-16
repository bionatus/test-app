<?php

namespace Tests\Feature\LiveApi\V2\Order\ItemOrder\CustomItem;

use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V2\Order\ItemOrder\CustomItemController;
use App\Models\CustomItem;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see CustomItemController */
class DeleteTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = LiveApiV2::LIVE_API_V2_ORDER_ITEM_ORDER_CUSTOM_ITEM_DELETE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $supplier   = Supplier::factory()->createQuietly();
        $customItem = CustomItem::factory()->usingSupplier($supplier)->create();
        $order      = Order::factory()->usingSupplier($supplier)->create();
        $itemOrder  = ItemOrder::factory()->usingOrder($order)->usingItem($customItem->item)->create();

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::SUPPLIER_CUSTOM_ITEM_ITEM_ORDER => $itemOrder]);

        $this->delete($route);
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, [
            'can:read,' . RouteParameters::ORDER,
        ]);
    }

    /** @test */
    public function it_deletes_a_supplier_custom_item()
    {
        $supplier   = Supplier::factory()->createQuietly();
        $staff      = Staff::factory()->usingSupplier($supplier)->create();
        $order      = Order::factory()->usingSupplier($supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);
        $customItem = CustomItem::factory()->usingSupplier($supplier)->create();
        $itemOrder  = ItemOrder::factory()->usingOrder($order)->usingItem($customItem->item)->create();
        $route      = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::SUPPLIER_CUSTOM_ITEM_ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDeleted($itemOrder);
        $this->assertDeleted($customItem);
        $this->assertDeleted($customItem->item);
    }

    /** @test */
    public function it_does_not_delete_a_custom_item_when_it_is_used_in_another_order()
    {
        $supplier     = Supplier::factory()->createQuietly();
        $staff        = Staff::factory()->usingSupplier($supplier)->create();
        $order        = Order::factory()->usingSupplier($supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);
        $anotherOrder = Order::factory()->usingSupplier($supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);
        $customItem   = CustomItem::factory()->usingSupplier($supplier)->create();
        $itemOrder    = ItemOrder::factory()->usingOrder($order)->usingItem($customItem->item)->create();
        ItemOrder::factory()->usingOrder($anotherOrder)->usingItem($customItem->item)->create();
        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::SUPPLIER_CUSTOM_ITEM_ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDeleted($itemOrder);
        $this->assertDatabaseHas(CustomItem::class, [
            'id'           => $customItem->getKey(),
            'creator_type' => Supplier::MORPH_ALIAS,
            'creator_id'   => $supplier->getKey(),
        ]);
        $this->assertDatabaseHas(Item::class, [
            'id'   => $customItem->item->getKey(),
            'type' => Item::TYPE_CUSTOM_ITEM,
        ]);
    }

    /** @test
     * @dataProvider itemTypeDataProvider
     */
    public function it_does_not_delete_a_different_items_to_custom_item($type)
    {
        $supplier  = Supplier::factory()->createQuietly();
        $staff     = Staff::factory()->usingSupplier($supplier)->create();
        $order     = Order::factory()->usingSupplier($supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);
        $item      = Item::factory()->create(['type' => $type]);
        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($item)->create();
        $route     = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::SUPPLIER_CUSTOM_ITEM_ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function itemTypeDataProvider(): array
    {
        return [
            [Item::TYPE_PART],
            [Item::TYPE_SUPPLY],
        ];
    }

    /** @test */
    public function it_does_not_deletes_an_item_that_belongs_to_another_order()
    {
        $supplier     = Supplier::factory()->createQuietly();
        $staff        = Staff::factory()->usingSupplier($supplier)->create();
        $order        = Order::factory()->usingSupplier($supplier)->pending()->create();
        $anotherOrder = Order::factory()->usingSupplier($supplier)->pending()->create();
        $customItem   = CustomItem::factory()->usingSupplier($supplier)->create();
        $itemOrder    = ItemOrder::factory()->usingOrder($anotherOrder)->usingItem($customItem->item)->create();
        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::SUPPLIER_CUSTOM_ITEM_ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
