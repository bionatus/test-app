<?php

namespace Tests\Feature\LiveApi\V2\Order\ItemOrder\ExtraItem;

use App\Constants\RequestKeys;
use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V2\Order\ItemOrder\ExtraItemController;
use App\Http\Requests\LiveApi\V2\Order\ItemOrder\ExtraItem\UpdateRequest;
use App\Http\Resources\LiveApi\V2\Order\ItemOrder\ExtraItem\BaseResource;
use App\Models\CustomItem;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\Supply;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see ExtraItemController */
class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = LiveApiV2::LIVE_API_V2_ORDER_ITEM_ORDER_EXTRA_ITEM_UPDATE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();

        $this->expectException(UnauthorizedHttpException::class);

        $this->patch(URL::route($this->routeName, [RouteParameters::ORDER => $order]));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:updateItems,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, UpdateRequest::class);
    }

    /** @test */
    public function it_updates_the_quantity_of_requested_item_order_of_supply_type()
    {
        $staff             = Staff::factory()->createQuietly();
        $order             = Order::factory()->usingSupplier($staff->supplier)->pending()->create();
        $supply            = Supply::factory()->visible()->create();
        $requestedQuantity = 5;
        $expectedQuantity  = 2;

        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($supply->item)->create([
            'quantity'           => $requestedQuantity,
            'quantity_requested' => $requestedQuantity,
        ]);

        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order]);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->patch($route, [
            RequestKeys::ITEMS => [
                [
                    "uuid"     => $itemOrder->getRouteKey(),
                    "quantity" => $expectedQuantity,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'id'                 => $itemOrder->getKey(),
            'uuid'               => $itemOrder->getRouteKey(),
            'order_id'           => $order->getKey(),
            'item_id'            => $supply->item->getKey(),
            'quantity'           => $expectedQuantity,
            'quantity_requested' => $requestedQuantity,
            'status'             => 'available',
        ]);
    }

    /** @test */
    public function it_updates_the_quantity_of_requested_item_order_of_custom_item_type_added_by_tech()
    {
        $staff           = Staff::factory()->createQuietly();
        $order           = Order::factory()->usingSupplier($staff->supplier)->pending()->create();
        $customItemsUser = CustomItem::factory()->create();

        $requestedQuantity = 5;
        $expectedQuantity  = 2;

        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($customItemsUser->item)->create([
            'quantity'           => $requestedQuantity,
            'quantity_requested' => $requestedQuantity,
        ]);

        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order]);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->patch($route, [
            RequestKeys::ITEMS => [
                [
                    "uuid"     => $itemOrder->getRouteKey(),
                    "quantity" => $expectedQuantity,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'id'                 => $itemOrder->getKey(),
            'uuid'               => $itemOrder->getRouteKey(),
            'order_id'           => $order->getKey(),
            'item_id'            => $customItemsUser->item->getKey(),
            'quantity'           => $expectedQuantity,
            'quantity_requested' => $requestedQuantity,
            'status'             => 'available',
        ]);
    }

    /** @test */
    public function it_updates_the_item_order_status_to_not_available_if_requested_quantity_is_zero()
    {
        $staff             = Staff::factory()->createQuietly();
        $order             = Order::factory()->usingSupplier($staff->supplier)->pending()->create();
        $supply            = Supply::factory()->visible()->create();
        $requestedQuantity = 5;
        $expectedQuantity  = 0;

        $itemOrder = ItemOrder::factory()->usingOrder($order)->available()->usingItem($supply->item)->create([
            'quantity'           => $requestedQuantity,
            'quantity_requested' => $requestedQuantity,
        ]);

        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order]);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->patch($route, [
            RequestKeys::ITEMS => [
                [
                    "uuid"     => $itemOrder->getRouteKey(),
                    "quantity" => $expectedQuantity,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'id'                 => $itemOrder->getKey(),
            'uuid'               => $itemOrder->getRouteKey(),
            'order_id'           => $order->getKey(),
            'item_id'            => $supply->item->getKey(),
            'quantity'           => $expectedQuantity,
            'quantity_requested' => $requestedQuantity,
            'status'             => 'not_available',
        ]);
    }
}
