<?php

namespace Tests\Feature\Api\V4\Account\Cart\CartItem;

use App;
use App\Actions\Models\Cart\GetCart;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V4\Account\Cart\CartItemController;
use App\Http\Requests\Api\V4\Account\Cart\CartItem\StoreRequest;
use App\Models\AirFilter;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CartSupplyCounter;
use App\Models\Part;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see CartItemController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V4_ACCOUNT_CART_ITEM_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_stores_cart_items_with_an_existing_cart()
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->usingUser($user)->create();

        $part = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $partItem     = $part->item;
        $partQuantity = 2;

        $supply         = Supply::factory()->create();
        $supplyItem     = $supply->item;
        $supplyQuantity = 3;

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::ITEMS => [
                [
                    "uuid"     => $partItem->getRouteKey(),
                    "quantity" => $partQuantity,
                ],
                [
                    "uuid"     => $supplyItem->getRouteKey(),
                    "quantity" => $supplyQuantity,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseCount(CartItem::tableName(), 2);
        $this->assertDatabaseHas(CartItem::tableName(), [
            'cart_id'  => $cart->getKey(),
            'item_id'  => $partItem->getKey(),
            'quantity' => $partQuantity,
        ]);
        $this->assertDatabaseHas(CartItem::tableName(), [
            'cart_id'  => $cart->getKey(),
            'item_id'  => $supplyItem->getKey(),
            'quantity' => $supplyQuantity,
        ]);
    }

    /** @test */
    public function it_stores_cart_items_without_an_existing_cart()
    {
        $user = User::factory()->create();

        $part = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $partItem     = $part->item;
        $partQuantity = 2;

        $supply         = Supply::factory()->create();
        $supplyItem     = $supply->item;
        $supplyQuantity = 3;

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::ITEMS => [
                [
                    "uuid"     => $partItem->getRouteKey(),
                    "quantity" => $partQuantity,
                ],
                [
                    "uuid"     => $supplyItem->getRouteKey(),
                    "quantity" => $supplyQuantity,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseCount(Cart::tableName(), 1);
        $this->assertDatabaseHas(Cart::tableName(), [
            'user_id' => $user->getKey(),
        ]);

        $cart = $user->cart;
        $this->assertDatabaseCount(CartItem::tableName(), 2);
        $this->assertDatabaseHas(CartItem::tableName(), [
            'cart_id'  => $cart->getKey(),
            'item_id'  => $partItem->getKey(),
            'quantity' => $partQuantity,
        ]);
        $this->assertDatabaseHas(CartItem::tableName(), [
            'cart_id'  => $cart->getKey(),
            'item_id'  => $supplyItem->getKey(),
            'quantity' => $supplyQuantity,
        ]);
    }

    /** @test */
    public function it_stores_cart_supply_counters_if_the_item_is_supply()
    {
        $user = User::factory()->create();
        Cart::factory()->usingUser($user)->create();

        $supply         = Supply::factory()->create();
        $supplyItem     = $supply->item;
        $supplyQuantity = 2;

        $otherSupply         = Supply::factory()->create();
        $otherSupplyItem     = $otherSupply->item;
        $otherSupplyQuantity = 3;

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::ITEMS => [
                [
                    "uuid"     => $supplyItem->getRouteKey(),
                    "quantity" => $supplyQuantity,
                ],
                [
                    "uuid"     => $otherSupplyItem->getRouteKey(),
                    "quantity" => $otherSupplyQuantity,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseCount(CartSupplyCounter::tableName(), 2);
        $this->assertDatabaseHas(CartSupplyCounter::tableName(), [
            'user_id'   => $user->getKey(),
            'supply_id' => $supply->getKey(),
        ]);
        $this->assertDatabaseHas(CartSupplyCounter::tableName(), [
            'user_id'   => $user->getKey(),
            'supply_id' => $otherSupply->getKey(),
        ]);
    }

    /** @test */
    public function it_does_not_store_a_cart_supply_counter_if_the_item_is_not_supply()
    {
        $user = User::factory()->create();
        Cart::factory()->usingUser($user)->create();

        $part = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $partItem     = $part->item;
        $partQuantity = 2;

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::ITEMS => [
                [
                    "uuid"     => $partItem->getRouteKey(),
                    "quantity" => $partQuantity,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseCount(CartSupplyCounter::tableName(), 0);
    }

    /** @test */
    public function it_calls_get_cart_action()
    {
        $user = User::factory()->create();

        $part = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $partItem     = $part->item;
        $partQuantity = 2;

        $action = Mockery::mock(GetCart::class);
        $action->shouldReceive('execute')->withNoArgs()->once();
        App::bind(GetCart::class, fn() => $action);

        $this->login($user);
        $route = URL::route($this->routeName);
        $this->post($route, [
            RequestKeys::ITEMS => [
                [
                    "uuid"     => $partItem->getRouteKey(),
                    "quantity" => $partQuantity,
                ],
            ],
        ]);
    }
}
