<?php

namespace Tests\Feature\Api\V4\Account\Cart;

use App;
use App\Actions\Models\Cart\GetCart;
use App\Constants\RouteNames;
use App\Http\Resources\Api\V4\Account\Cart\BaseResource;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see CartController */
class ShowTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V4_ACCOUNT_CART_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_returns_the_cart_information()
    {
        Carbon::setTestNow('2022-12-05 10:00:00');

        $user = User::factory()->create();

        Cart::factory()->usingUser($user)->create();

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $data     = Collection::make($response->json('data'));

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertEquals(0, $data['total_items']);
        $this->assertEquals(Carbon::now()->startOfSecond()->toISOString(), $data['created_at']);
    }

    /** @test */
    public function it_calls_get_cart_method_to_retrieve_the_cart()
    {
        $user = User::factory()->create();

        $cart = Cart::factory()->usingUser($user)->create();

        $action = Mockery::mock(GetCart::class);
        $action->shouldReceive('execute')->withNoArgs()->once()->andReturn($cart);
        App::bind(GetCart::class, fn() => $action);

        $this->login($user);
        $route = URL::route($this->routeName);
        $this->get($route);
    }
}

