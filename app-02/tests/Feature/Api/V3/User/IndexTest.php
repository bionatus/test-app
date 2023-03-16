<?php

namespace Tests\Feature\Api\V3\User;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\UserController;
use App\Http\Requests\Api\V3\User\IndexRequest;
use App\Http\Resources\Api\V3\User\BaseResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see UserController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_USER_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $route = URL::route($this->routeName);
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);
        $this->get($route);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, IndexRequest::class);
    }

    /** @test */
    public function it_display_a_list_of_users_ordered_alphabetically()
    {
        $expectedUsers = Collection::make([]);
        $expectedUsers->add(User::factory()->create(['first_name' => 'John B']));
        $expectedUsers->add(User::factory()->create(['first_name' => 'John C', 'last_name' => 'Acme']));
        $expectedUsers->prepend(User::factory()->create(['first_name' => 'John A', 'last_name' => 'Button']));
        $expectedUsers->prepend(User::factory()->create(['first_name' => 'John A', 'last_name' => 'Acme']));

        $search = 'John';
        $route  = URL::route($this->routeName, [RequestKeys::SEARCH_STRING => $search]);

        $this->login();
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);

        $data = Collection::make($response->json('data'));

        $data->each(function(array $rawUser, int $index) use ($expectedUsers) {
            $this->assertEquals($expectedUsers->get($index)->getRouteKey(), $rawUser['id']);
        });
    }

    /** @test */
    public function it_can_search_for_users_by_full_or_public_name()
    {
        $userFirst  = User::factory()->create(['first_name' => 'first znamez']);
        $userLast   = User::factory()->create(['last_name' => 'last znamez']);
        $userPublic = User::factory()->create(['public_name' => 'public znamez']);

        $users = Collection::make([$userFirst->getKey(), $userLast->getKey(), $userPublic->getKey()]);

        User::factory()->count(10)->create();

        $route = URL::route($this->routeName, [RequestKeys::SEARCH_STRING => 'znamez']);

        $this->login();
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $data          = Collection::make($response->json('data'));
        $expectedUsers = $data->pluck('id');

        $this->assertEqualsCanonicalizing($expectedUsers, $users);
    }

    /** @test */
    public function it_does_not_show_disabled_users()
    {
        User::factory()->count(2)->create(['first_name' => 'John']);
        User::factory()->count(1)->create(['first_name' => 'John', 'disabled_at' => Carbon::now()]);

        $route = URL::route($this->routeName, [RequestKeys::SEARCH_STRING => 'John']);

        $this->login();
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $data = $response->json('data');

        $this->assertCount(2, $data);
    }

    /** @test */
    public function it_does_not_show_the_logged_user()
    {
        User::factory()->count(2)->create(['first_name' => 'John']);
        $userLogged = User::factory()->create(['first_name' => 'John']);
        $route      = URL::route($this->routeName, [RequestKeys::SEARCH_STRING => 'John']);

        $this->login($userLogged);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $data = Collection::make($response->json('data'));

        $this->assertCount(2, $data);
        $this->assertNotContains($userLogged->getKey(), $data->pluck('id'));
    }
}
