<?php

namespace Tests\Feature\Api\V4\Order\ItemOrder\ExtraItem;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V4\Order\ItemOrder\ExtraItemController;
use App\Http\Requests\Api\V4\Order\ItemOrder\ExtraItem\StoreRequest;
use App\Http\Resources\Api\V4\Order\ItemOrder\ExtraItem\BaseResource;
use App\Models\CartSupplyCounter;
use App\Models\CustomItem;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see ExtraItemController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V4_ORDER_ITEM_ORDER_EXTRA_ITEM_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);
        $order = Order::factory()->create();

        $this->post(URL::route($this->routeName, [$order]));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:updateItemOrder,' . RouteParameters::ORDER]);

    }

    /** @test */
    public function it_returns_the_correct_base_resource_schema()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($user)->usingSupplier($supplier)->pendingApproval()->create();

        $supply     = Supply::factory()->create();
        $supplyItem = $supply->item;

        $this->login($user);
        $route    = URL::route($this->routeName, [$order]);
        $response = $this->post($route, [
            RequestKeys::ITEMS => [
                [
                    'uuid'     => $supplyItem->getRouteKey(),
                    'quantity' => 3,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
    }

    /** @test */
    public function it_stores_supplies_and_user_custom_items_in_an_order()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($user)->usingSupplier($supplier)->pendingApproval()->create();

        $supply         = Supply::factory()->create();
        $supplyItem     = $supply->item;
        $supplyQuantity = 3;

        $anotherSupply         = Supply::factory()->create();
        $anotherSupplyItem     = $anotherSupply->item;
        $anotherSupplyQuantity = 2;

        $userCustomItem         = CustomItem::factory()->usingUser($user)->create();
        $userCustomItemItem     = $userCustomItem->item;
        $userCustomItemQuantity = 1;

        $this->login($user);
        $route    = URL::route($this->routeName, [$order]);
        $response = $this->post($route, [
            RequestKeys::ITEMS => [
                [
                    'uuid'     => $supplyItem->getRouteKey(),
                    'quantity' => $supplyQuantity,
                ],
                [
                    'uuid'     => $anotherSupplyItem->getRouteKey(),
                    'quantity' => $anotherSupplyQuantity,
                ],
                [
                    'uuid'     => $userCustomItemItem->getRouteKey(),
                    'quantity' => $userCustomItemQuantity,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseCount(ItemOrder::tableName(), 3);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'order_id'           => $order->getKey(),
            'item_id'            => $supplyItem->getKey(),
            'quantity'           => $supplyQuantity,
            'quantity_requested' => $supplyQuantity,
        ]);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'order_id'           => $order->getKey(),
            'item_id'            => $anotherSupplyItem->getKey(),
            'quantity'           => $anotherSupplyQuantity,
            'quantity_requested' => $anotherSupplyQuantity,
        ]);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'order_id'           => $order->getKey(),
            'item_id'            => $userCustomItemItem->getKey(),
            'quantity'           => $userCustomItemQuantity,
            'quantity_requested' => $userCustomItemQuantity,
        ]);
    }

    /** @test */
    public function it_stores_cart_supply_counters()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($user)->usingSupplier($supplier)->pendingApproval()->create();

        $supply         = Supply::factory()->create();
        $supplyItem     = $supply->item;
        $supplyQuantity = 3;

        $anotherSupply         = Supply::factory()->create();
        $anotherSupplyItem     = $anotherSupply->item;
        $anotherSupplyQuantity = 2;

        $this->login($user);
        $route    = URL::route($this->routeName, [$order]);
        $response = $this->post($route, [
            RequestKeys::ITEMS => [
                [
                    "uuid"     => $supplyItem->getRouteKey(),
                    "quantity" => $supplyQuantity,
                ],
                [
                    "uuid"     => $anotherSupplyItem->getRouteKey(),
                    "quantity" => $anotherSupplyQuantity,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(CartSupplyCounter::tableName(), [
            'user_id'   => $user->getKey(),
            'supply_id' => $supplyItem->getKey(),
        ]);

        $this->assertDatabaseHas(CartSupplyCounter::tableName(), [
            'user_id'   => $user->getKey(),
            'supply_id' => $anotherSupplyItem->getKey(),
        ]);
    }

    /** @test */
    public function it_adds_a_new_item_order_with_initial_request_zero_if_the_item_was_already_added()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($user)->usingSupplier($supplier)->pendingApproval()->create();

        $supply         = Supply::factory()->create();
        $supplyItem     = $supply->item;
        $supplyQuantity = 3;

        ItemOrder::factory()->usingOrder($order)->usingItem($supplyItem)->create(['quantity' => 2]);

        $this->login($user);
        $route    = URL::route($this->routeName, [$order]);
        $response = $this->post($route, [
            RequestKeys::ITEMS => [
                [
                    'uuid'     => $supplyItem->getRouteKey(),
                    'quantity' => $supplyQuantity,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseCount(ItemOrder::tableName(), 2);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'order_id'        => $order->getKey(),
            'item_id'         => $supplyItem->getKey(),
            'quantity'        => 2,
            'initial_request' => true,
        ]);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'order_id'           => $order->getKey(),
            'item_id'            => $supplyItem->getKey(),
            'quantity'           => 3,
            'quantity_requested' => 3,
            'initial_request'    => false,
        ]);
    }
}
