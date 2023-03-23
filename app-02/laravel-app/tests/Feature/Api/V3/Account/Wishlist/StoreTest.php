<?php

namespace Tests\Feature\Api\V3\Account\Wishlist;

use App;
use App\Actions\Models\Wishlist\MakeNameUnique;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Requests\Api\V3\Account\Wishlist\StoreRequest;
use App\Http\Resources\Api\V3\Account\Wishlist\BaseResource;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see WishlistController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_ACCOUNT_WISHLIST_STORE;

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
    public function it_return_the_correct_base_resource_schema()
    {
        $user = User::factory()->create();

        $this->login($user);
        $route = URL::route($this->routeName);

        $response = $this->post($route, [
            RequestKeys::NAME => 'fake name',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
    }

    /** @test */
    public function it_stores_a_wishlist()
    {
        $user = User::factory()->create();

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::NAME => 'fake name',
        ]);

        Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseCount(Wishlist::tableName(), 1);
        $this->assertDatabaseHas(Wishlist::tableName(), ['user_id' => $user->getKey()]);
    }

    /** @test */
    public function it_calls_a_method_to_generate_an_unique_wishlist_name_for_each_user()
    {
        $user = User::factory()->create();
        Wishlist::factory()->usingUser($user)->create();

        $makeUniqueName = Mockery::mock(MakeNameUnique::class);
        $makeUniqueName->shouldReceive('execute')->withAnyArgs()->once();
        App::bind(MakeNameUnique::class, fn() => $makeUniqueName);

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::NAME => 'fake name',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
    }
}
