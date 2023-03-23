<?php

namespace Tests\Unit\Http\Middleware;

use App;
use App\Http\Middleware\VerifyCartNotEmpty;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class VerifyCartNotEmptyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_should_reject_if_user_cart_is_empty()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        Cart::factory()->usingUser($user)->usingSupplier($supplier)->create();

        $this->login($user);

        $requestMock = Mockery::mock(Request::class);

        $middleware = App::make(VerifyCartNotEmpty::class);

        $response = $middleware->handle($requestMock, fn() => null);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /** @test */
    public function it_should_reject_if_user_has_no_cart()
    {
        $user     = User::factory()->create();

        $this->login($user);

        $requestMock = Mockery::mock(Request::class);

        $middleware = App::make(VerifyCartNotEmpty::class);

        $response = $middleware->handle($requestMock, fn() => null);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /** @test */
    public function it_should_accept_if_user_has_a_cart_with_items()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $cart     = Cart::factory()->usingUser($user)->usingSupplier($supplier)->create();
        CartItem::factory()->usingCart($cart)->create();

        $this->login($user);

        $requestMock = Mockery::mock(Request::class);

        $middleware = App::make(VerifyCartNotEmpty::class);

        $response = $middleware->handle($requestMock, fn() => null);

        $this->assertNull($response);
    }
}
