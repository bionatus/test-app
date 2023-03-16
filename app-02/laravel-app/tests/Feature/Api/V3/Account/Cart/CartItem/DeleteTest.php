<?php

namespace Tests\Feature\Api\V3\Account\Cart\CartItem;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see CartItemController */
class DeleteTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V3_ACCOUNT_CART_ITEM_DELETE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);
        $cartItem = CartItem::factory()->create();

        $this->delete(URL::route($this->routeName, [$cartItem]));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:delete,' . RouteParameters::CART_ITEM]);
    }

    /** @test */
    public function it_deletes_a_cart_item_of_the_user_cart()
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->usingUser($user)->create();

        $cartItem = CartItem::factory()->usingCart($cart)->create();

        $this->login($user);

        $response = $this->delete(URL::route($this->routeName,
            [RouteParameters::CART_ITEM => $cartItem->getRouteKey()]));

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(CartItem::tableName(), [
            'cart_id' => $cart->getKey(),
            'item_id' => $cartItem->item_id,
        ]);
    }
}
