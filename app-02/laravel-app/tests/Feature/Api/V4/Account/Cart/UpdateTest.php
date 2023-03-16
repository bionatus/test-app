<?php

namespace Tests\Feature\Api\V4\Account\Cart;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Requests\Api\V4\Account\Cart\UpdateRequest;
use App\Http\Resources\Api\V4\Account\Cart\BaseResource;
use App\Models\Cart;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see CartController */
class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V4_ACCOUNT_CART_UPDATE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->patch(URL::route($this->routeName));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, UpdateRequest::class);
    }

    /** @test */
    public function it_return_the_correct_base_resource_schema()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();

        $this->login($user);
        $route = URL::route($this->routeName);

        $response = $this->patch($route, [
            RequestKeys::SUPPLIER => $supplier->getRouteKey(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
    }

    /** @test */
    public function it_creates_the_cart_if_it_is_not_created()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();

        $this->login($user);
        $route = URL::route($this->routeName);
        $response = $this->patch($route, [RequestKeys::SUPPLIER => $supplier->getRouteKey()]);
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(Cart::tableName(), [
            'user_id'     => $user->getKey(),
            'supplier_id' => $supplier->getKey(),
        ]);
    }

    /** @test */
    public function it_updates_the_cart()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        Cart::factory()->usingUser($user)->create();

        $this->login($user);
        $route = URL::route($this->routeName);
        $response = $this->patch($route, [RequestKeys::SUPPLIER => $supplier->getRouteKey()]);
        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas(Cart::tableName(), [
            'user_id'     => $user->getKey(),
            'supplier_id' => $supplier->getKey(),
        ]);
    }
}
