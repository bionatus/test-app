<?php

namespace Tests\Feature\Api\V3\Account\Cart;

use App\Constants\RouteNames;
use App\Http\Resources\Api\V3\Account\Cart\BaseResource;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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

    private string $routeName = RouteNames::API_V3_ACCOUNT_CART_SHOW;

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
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertEquals(0, $data['total_items']);
        $this->assertEquals(Carbon::now()->startOfSecond()->toISOString(), $data['created_at']);
    }
}

