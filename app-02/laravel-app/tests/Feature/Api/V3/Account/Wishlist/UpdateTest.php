<?php

namespace Tests\Feature\Api\V3\Account\Wishlist;

use App;
use App\Actions\Models\Wishlist\MakeNameUnique;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Requests\Api\V3\Account\Wishlist\UpdateRequest;
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
class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_ACCOUNT_WISHLIST_UPDATE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $wishlist = Wishlist::factory()->create();

        $this->patch(URL::route($this->routeName, [RouteParameters::WISHLIST => $wishlist->getRouteKey()]));
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
        $wishlist = Wishlist::factory()->usingUser($user)->create();

        $this->login($user);
        $route = URL::route($this->routeName, [RouteParameters::WISHLIST => $wishlist->getRouteKey()]);

        $response = $this->patch($route, [
            RequestKeys::NAME => 'fake name',
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
    }

    /** @test */
    public function it_updates_a_wishlist()
    {
        $user     = User::factory()->create();
        $wishlist = Wishlist::factory()->usingUser($user)->create(['name' => 'old fake name']);

        $this->login($user);
        $route    = URL::route($this->routeName, [RouteParameters::WISHLIST => $wishlist->getRouteKey()]);
        $response = $this->patch($route, [
            RequestKeys::NAME => $name = 'new fake name',
        ]);

        Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseCount(Wishlist::tableName(), 1);
        $this->assertDatabaseHas(Wishlist::tableName(), ['user_id' => $user->getKey(), 'name' => $name]);
    }

    /** @test */
    public function it_calls_a_method_to_generate_an_unique_wishlist_name_for_each_user_if_wishlist_changed_the_name()
    {
        $user     = User::factory()->create();
        $wishlist = Wishlist::factory()->usingUser($user)->create(['name' => 'fake name']);

        $makeUniqueName = Mockery::mock(MakeNameUnique::class);
        $makeUniqueName->shouldReceive('execute')->withAnyArgs()->once();
        App::bind(MakeNameUnique::class, fn() => $makeUniqueName);

        $this->login($user);
        $route    = URL::route($this->routeName, [RouteParameters::WISHLIST => $wishlist->getRouteKey()]);
        $response = $this->patch($route, [
            RequestKeys::NAME => 'fake new name',
        ]);

        $response->assertStatus(Response::HTTP_OK);
    }
}
