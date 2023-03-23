<?php

namespace Tests\Feature\LiveApi\V2\Order\ItemOrder\CustomItem;

use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Http\Resources\LiveApi\V2\Order\ItemOrder\ExtraItem\BaseResource;
use App\Models\CustomItem;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Part;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\Supply;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see CustomItemController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = LiveApiV2::LIVE_API_V2_ORDER_ITEM_ORDER_CUSTOM_ITEM_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName, $order));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:read,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_display_a_list_of_item_order_with_supplier_custom_items()
    {
        $parts      = Part::factory()->count(3)->create();
        $staff      = Staff::factory()->createQuietly();
        $supplier   = $staff->supplier;
        $order      = Order::factory()->usingSupplier($supplier)->create();
        $orderItems = Collection::make();

        $supplies = Supply::factory()->visible()->count(5)->create();
        $supplies->each(function(Supply $supply) use ($order, &$orderItems) {
            ItemOrder::factory()->usingOrder($order)->usingItem($supply->item)->create();
        });

        $customItems = CustomItem::factory()->count(4)->create();
        $customItems->each(function(CustomItem $customItem) use ($order, &$orderItems) {
            ItemOrder::factory()->usingOrder($order)->usingItem($customItem->item)->create();
        });

        $customItemsSuppliers = CustomItem::factory()->usingSupplier($supplier)->count(10)->create();
        $customItemsSuppliers->each(function(CustomItem $customItem) use ($order, &$orderItems) {
            $orderItems->push(ItemOrder::factory()->usingOrder($order)->usingItem($customItem->item)->create());
        });

        $parts->each(function(Part $part) use ($order) {
            ItemOrder::factory()->usingOrder($order)->usingItem($part->item)->create();
        });

        $route = URL::route($this->routeName, $order);

        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->get($route);
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount(10, $data);

        $data->each(function(array $rawItem, int $index) use ($orderItems) {
            $orderItem = $orderItems->get($index);
            $this->assertSame($orderItem->getRouteKey(), $rawItem['id']);
        });
    }
}
