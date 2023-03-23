<?php

namespace Tests\Feature\LiveApi\V2\Order\InProgress\ItemOrder\ExtraItem;

use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V2\Order\InProgress\ItemOrder\ExtraItemController;
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

/** @see ExtraItemController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = LiveApiV2::LIVE_API_V2_ORDER_IN_PROGRESS_ITEM_ORDER_EXTRA_ITEM_INDEX;

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
    public function it_display_a_list_of_item_order_with_supply_type_or_user_custom_item_type()
    {
        $staff      = Staff::factory()->createQuietly();
        $supplier   = $staff->supplier;
        $order      = Order::factory()->usingSupplier($supplier)->create();
        $orderItems = Collection::make();

        $supplies = Supply::factory()->visible()->count(5)->create();
        $supplies->each(function(Supply $supply) use ($order, &$orderItems) {
            $orderItems->push(ItemOrder::factory()
                ->usingOrder($order)
                ->usingItem($supply->item)
                ->notInitialRequest()
                ->create());
        });

        $customItems = CustomItem::factory()->count(4)->create();
        $customItems->each(function(CustomItem $customItem) use ($order, &$orderItems) {
            $orderItems->push(ItemOrder::factory()
                ->usingOrder($order)
                ->usingItem($customItem->item)
                ->notInitialRequest()
                ->create());
        });

        $customItemsSuppliers = CustomItem::factory()->usingSupplier($supplier)->count(10)->create();
        $customItemsSuppliers->each(function(CustomItem $customItem) use ($order, &$orderItems) {
            ItemOrder::factory()->usingOrder($order)->usingItem($customItem->item)->notInitialRequest()->create();
        });

        $parts = Part::factory()->count(3)->create();
        $parts->each(function(Part $part) use ($order) {
            ItemOrder::factory()->usingOrder($order)->usingItem($part->item)->notInitialRequest()->create();
        });

        $route = URL::route($this->routeName, $order);

        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->get($route);
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals(9, $response->json('meta.total'));

        $data->each(function(array $rawItem, int $index) use ($orderItems) {
            $orderItem = $orderItems->get($index);
            $this->assertSame($orderItem->getRouteKey(), $rawItem['id']);
        });
    }

    /** @test */
    public function it_display_a_list_of_item_order_not_added_on_the_initial_request()
    {
        $staff      = Staff::factory()->createQuietly();
        $supplier   = $staff->supplier;
        $order      = Order::factory()->usingSupplier($supplier)->create();
        $orderItems = Collection::make();

        $initialSupplies = Supply::factory()->visible()->count(3)->create();
        $initialSupplies->each(function(Supply $supply) use ($order){
            ItemOrder::factory()->usingOrder($order)->usingItem($supply->item)->create();
        });

        $supplies = Supply::factory()->visible()->count(5)->create();
        $supplies->each(function(Supply $supply) use ($order, &$orderItems) {
            $orderItems->push(ItemOrder::factory()
                ->usingOrder($order)
                ->usingItem($supply->item)
                ->notInitialRequest()
                ->create());
        });

        ItemOrder::factory()->usingOrder($order)->create();

        $route = URL::route($this->routeName, $order);

        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->get($route);
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount(5, $data);

        $data->each(function(array $rawItem, int $index) use ($orderItems) {
            $orderItem = $orderItems->get($index);
            $this->assertSame($orderItem->getRouteKey(), $rawItem['id']);
        });
    }
}
