<?php

namespace Tests\Feature\Api\V3\Account\Cart\CartItem;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Account\Cart\CartItemController;
use App\Http\Requests\Api\V3\Account\Cart\CartItem\StoreRequest;
use App\Http\Resources\Api\V3\Account\Cart\CartItem\BaseResource;
use App\Models\AirFilter;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CartSupplyCounter;
use App\Models\Item;
use App\Models\Part;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    private string $routeName = RouteNames::API_V3_ACCOUNT_CART_ITEM_STORE;

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
    public function it_stores_a_cart_item_with_an_existing_cart()
    {
        $user = User::factory()->create();
        $part = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $item     = $part->item;
        $quantity = 3;
        $cart     = Cart::factory()->usingUser($user)->create();

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::ITEM     => $item->getRouteKey(),
            RequestKeys::QUANTITY => $quantity,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $this->assertDatabaseCount(CartItem::tableName(), 1);
        $this->assertDatabaseHas(CartItem::tableName(), [
            'cart_id'  => $cart->getKey(),
            'item_id'  => $item->getKey(),
            'quantity' => $quantity,
        ]);
    }

    /** @test */
    public function it_stores_a_cart_item_without_an_existing_cart()
    {
        $user = User::factory()->create();
        $part = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $item     = $part->item;
        $quantity = 3;

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::ITEM     => $item->getRouteKey(),
            RequestKeys::QUANTITY => $quantity,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseCount(Cart::tableName(), 1);
        $this->assertDatabaseHas(Cart::tableName(), [
            'user_id' => $user->getKey(),
        ]);
    }

    /** @test */
    public function it_stores_a_cart_supply_counter_if_the_item_is_supply()
    {
        $user     = User::factory()->create();
        $item     = Item::factory()->supply()->create();
        $supply   = Supply::factory()->create([Supply::keyName() => $item->getKey()]);
        $quantity = 3;

        $this->login($user);
        $route = URL::route($this->routeName);
        $this->post($route, [
            RequestKeys::ITEM     => $item->getRouteKey(),
            RequestKeys::QUANTITY => $quantity,
        ]);

        $this->assertDatabaseCount(CartSupplyCounter::tableName(), 1);
        $this->assertDatabaseHas(CartSupplyCounter::tableName(), [
            'user_id'   => $user->getKey(),
            'supply_id' => $supply->getKey(),
        ]);
    }

    /** @test */
    public function it_does_not_store_a_cart_supply_counter_if_the_item_is_not_supply()
    {
        $user     = User::factory()->create();
        $item     = Item::factory()->part()->create();
        $quantity = 3;

        $this->login($user);
        $route = URL::route($this->routeName);
        $this->post($route, [
            RequestKeys::ITEM     => $item->getRouteKey(),
            RequestKeys::QUANTITY => $quantity,
        ]);

        $this->assertDatabaseCount(CartSupplyCounter::tableName(), 0);
    }
}
