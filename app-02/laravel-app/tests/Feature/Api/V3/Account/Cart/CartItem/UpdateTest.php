<?php

namespace Tests\Feature\Api\V3\Account\Cart\CartItem;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\Account\Cart\CartItemController;
use App\Http\Requests\Api\V3\Account\Cart\CartItem\UpdateRequest;
use App\Http\Resources\Api\V3\Account\Cart\CartItem\BaseResource;
use App\Models\AirFilter;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Part;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see CartItemController */
class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;
    use WithFaker;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_ACCOUNT_CART_ITEM_UPDATE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $cartItem = CartItem::factory()->create();
        $route    = URL::route($this->routeName, [$cartItem]);

        $this->patch($route);
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:update,' . RouteParameters::CART_ITEM]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, UpdateRequest::class);
    }

    /** @test */
    public function it_can_update_its_cart_item_quantity()
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->usingUser($user)->create();

        $part = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();

        $cartItem = CartItem::factory()->usingCart($cart)->usingItem($part->item)->create(['quantity' => 20]);

        $this->login($user);

        $route = URL::route($this->routeName, [RouteParameters::CART_ITEM => $cartItem->getRouteKey()]);

        $response = $this->patch($route, [
            RequestKeys::QUANTITY => $newQuantity = 77,
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($data['id'], $cartItem->getRouteKey());
        $this->assertEquals($data['quantity'], $newQuantity);
    }
}
