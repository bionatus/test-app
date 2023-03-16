<?php

namespace Tests\Feature\Api\V3\Post\User;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\Post\UserController;
use App\Http\Resources\Api\V2\UserResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see UserController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_POST_USER_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $post = Post::factory()->create();

        $route = URL::route($this->routeName, [RouteParameters::POST => $post]);
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);
        $this->get($route);
    }

    /** @test */
    public function it_display_a_list_of_users_related_to_a_post()
    {
        User::factory()->count(5)->create();
        $userOwner  = User::factory()->create();
        $post       = Post::factory()->usingUser($userOwner)->create();
        $comments   = Comment::factory()->usingPost($post)->count(3)->create();
        $commenters = $comments->pluck('user_id');

        $expectedUser = $commenters->push($userOwner->getKey());

        $route = URL::route($this->routeName, [RouteParameters::POST => $post]);

        $this->login();
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(UserResource::jsonSchema(), true), $response);

        $data = Collection::make($response->json('data'));

        $this->assertEqualsCanonicalizing($expectedUser, $data->pluck('id'));
    }

    /** @test */
    public function it_display_a_list_of_users_ordered_alphabetically()
    {
        User::factory()->count(5)->create();
        $userA     = User::factory()->create(['first_name' => 'John C', 'last_name' => 'Acme']);
        $userB     = User::factory()->create(['first_name' => 'John A', 'last_name' => 'Button']);
        $userC     = User::factory()->create(['first_name' => 'John A', 'last_name' => 'Acme']);
        $userOwner = User::factory()->create(['first_name' => 'John B']);

        $post = Post::factory()->usingUser($userOwner)->create();

        Comment::factory()->usingPost($post)->usingUser($userA)->create();
        Comment::factory()->usingPost($post)->usingUser($userB)->create();
        Comment::factory()->usingPost($post)->usingUser($userC)->create();

        $expectedUsers = Collection::make([]);
        $expectedUsers->add($userC);
        $expectedUsers->add($userB);
        $expectedUsers->add($userOwner);
        $expectedUsers->add($userA);

        $route = URL::route($this->routeName, [RouteParameters::POST => $post]);

        $this->login(User::factory()->create());
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(UserResource::jsonSchema(), true), $response);

        $data = Collection::make($response->json('data'));

        $data->each(function(array $rawUser, int $index) use ($expectedUsers) {
            $this->assertEquals($expectedUsers->get($index)->getRouteKey(), $rawUser['id']);
        });
    }

    /** @test */
    public function it_does_not_show_disabled_users()
    {
        User::factory()->count(5)->create();
        $disabledUser = User::factory()->disabled(Carbon::now())->create();

        $postOwnerUser = User::factory()->create();
        $post          = Post::factory()->usingUser($postOwnerUser)->create();

        $comments = Comment::factory()->usingPost($post)->count(3)->create();
        Comment::factory()->usingPost($post)->usingUser($disabledUser)->create();

        $route = URL::route($this->routeName, [RouteParameters::POST => $post]);

        $userLogged = $comments->first()->user;
        $this->login($userLogged);
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(UserResource::jsonSchema(), true), $response);

        $data = Collection::make($response->json('data'));

        $this->assertCount(3, $data);
        $this->assertNotContains($userLogged->getRouteKey(), $data->pluck('id'));
    }

    /** @test */
    public function it_does_not_show_the_logged_user()
    {
        User::factory()->count(5)->create();
        $postOwnerUser = User::factory()->create();
        $post          = Post::factory()->usingUser($postOwnerUser)->create();
        Comment::factory()->usingPost($post)->count(3)->create();

        $route = URL::route($this->routeName, [RouteParameters::POST => $post]);

        $this->login($postOwnerUser);
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(UserResource::jsonSchema(), true), $response);

        $data = Collection::make($response->json('data'));

        $this->assertCount(3, $data);
        $this->assertNotContains($postOwnerUser->getRouteKey(), $data->pluck('id'));
    }
}
