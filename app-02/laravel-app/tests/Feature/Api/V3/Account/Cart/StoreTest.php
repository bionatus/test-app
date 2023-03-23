<?php

namespace Tests\Feature\Api\V3\Account\Cart;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Requests\Api\V3\Account\Cart\StoreRequest;
use App\Http\Resources\Api\V3\Account\Cart\BaseResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CartSupplyCounter;
use App\Models\Item;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see CartController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_ACCOUNT_CART_STORE;

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
    public function it_returns_the_correct_base_resource_schema()
    {
        $user = User::factory()->create();
        $item = Item::factory()->part()->create();

        $this->login($user);
        $route = URL::route($this->routeName);

        $response = $this->post($route, [
            RequestKeys::ITEMS => [
                [
                    "uuid"     => $item->getRouteKey(),
                    "quantity" => 2,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
    }

    /** @test */
    public function it_stores_the_cart_items()
    {
        $user        = User::factory()->create();
        $itemOne     = Item::factory()->part()->create();
        $itemTwo     = Item::factory()->part()->create();
        $quantityOne = 1;
        $quantityTwo = 2;

        $this->login($user);
        $route = URL::route($this->routeName);
        $this->post($route, [
            RequestKeys::ITEMS => [
                [
                    "uuid"     => $itemOne->getRouteKey(),
                    "quantity" => $quantityOne,
                ],
                [
                    "uuid"     => $itemTwo->getRouteKey(),
                    "quantity" => $quantityTwo,
                ],
            ],
        ]);

        $this->assertDatabaseCount(Cart::tableName(), 1);
        $this->assertDatabaseHas(Cart::tableName(), [
            'user_id' => $user->getKey(),
        ]);

        $this->assertDatabaseCount(CartItem::tableName(), 2);
        $this->assertDatabaseHas(CartItem::tableName(), [
            'item_id'  => $itemOne->getKey(),
            'quantity' => $quantityOne,
        ]);

        $this->assertDatabaseHas(CartItem::tableName(), [
            'item_id'  => $itemTwo->getKey(),
            'quantity' => $quantityTwo,
        ]);
    }

    /** @test */
    public function it_stores_a_cart_supply_counter_if_the_item_is_a_supply()
    {
        $user        = User::factory()->create();
        $itemOne     = Item::factory()->supply()->create();
        $itemTwo     = Item::factory()->supply()->create();
        $supplyOne   = Supply::factory()->create(['id' => $itemOne->getKey()]);
        $supplyTwo   = Supply::factory()->create(['id' => $itemTwo->getKey()]);
        $quantityOne = 1;
        $quantityTwo = 2;

        $this->login($user);
        $route = URL::route($this->routeName);
        $this->post($route, [
            RequestKeys::ITEMS => [
                [
                    "uuid"     => $itemOne->getRouteKey(),
                    "quantity" => $quantityOne,
                ],
                [
                    "uuid"     => $itemTwo->getRouteKey(),
                    "quantity" => $quantityTwo,
                ],
            ],
        ]);

        $this->assertDatabaseCount(CartSupplyCounter::tableName(), 2);
        $this->assertDatabaseHas(CartSupplyCounter::tableName(), [
            'user_id'   => $user->getKey(),
            'supply_id' => $supplyOne->getKey(),
        ]);
        $this->assertDatabaseHas(CartSupplyCounter::tableName(), [
            'user_id'   => $user->getKey(),
            'supply_id' => $supplyTwo->getKey(),
        ]);
    }
}
