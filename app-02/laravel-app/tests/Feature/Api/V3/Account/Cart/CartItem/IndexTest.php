<?php

namespace Tests\Feature\Api\V3\Account\Cart\CartItem;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Account\Cart\CartItemController;
use App\Http\Resources\Api\V3\Account\Cart\CartItem\BaseResource;
use App\Models\AirFilter;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Part;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see CartItemController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_ACCOUNT_CART_ITEM_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_returns_a_list_of_cart_items()
    {
        $user      = User::factory()->create();
        $cart      = Cart::factory()->usingUser($user)->create();
        $cartItems = Collection::make();
        $part      = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $cartItems->push(CartItem::factory()->usingCart($cart)->usingItem($part->item)->create());

        $anotherPart = Part::factory()->create();
        AirFilter::factory()->usingPart($anotherPart)->create();
        $cartItems->push(CartItem::factory()->usingCart($cart)->usingItem($anotherPart->item)->create());

        $supply = Supply::factory()->create();
        $cartItems->push(CartItem::factory()->usingCart($cart)->usingItem($supply->item)->create());

        $partNotInCart = Part::factory()->create();
        CartItem::factory()->usingItem($partNotInCart->item)->create();

        $route = URL::route($this->routeName);
        $this->login($user);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $data = Collection::make($response->json('data'));
        $this->assertCount(3, $data);

        $data->each(function(array $rawOrderItem, int $index) use ($cartItems) {
            $cartItem = $cartItems->get($index);
            $this->assertSame($cartItem->getRouteKey(), $rawOrderItem['id']);
        });
    }

    /** @test */
    public function it_creates_a_cart_for_the_user_if_not_exists()
    {
        $user = User::factory()->create();

        $route = URL::route($this->routeName);
        $this->login($user);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $this->assertDatabaseHas(Cart::tableName(), [
            'user_id' => $user->getKey(),
        ]);
    }
}

