<?php

namespace Tests\Feature\Api\V3\User\Post;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\User\PostController;
use App\Http\Resources\Api\V3\User\Post\BaseResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see PostController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_USER_POST_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $user = User::factory()->create();
        $this->get(URL::route($this->routeName, [$user]));
    }

    /** @test */
    public function it_returns_a_list_of_user_posts()
    {
        $user  = User::factory()->create();
        $posts = Post::factory()->usingUser($user)->count(5)->create();
        Post::factory()->count(5)->create();

        $this->login();
        $response = $this->get(URL::route($this->routeName, [$user]));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertcount($response->json('meta.total'), $posts);

        $data            = Collection::make($response->json('data'));
        $expectedUserIds = $data->pluck('user.id');

        $expectedUserIds->each(function(int $expectedUserId) use ($user) {
            $this->assertSame($expectedUserId, $user->getKey());
        });
    }

    /** @test */
    public function it_returns_a_list_of_posts_ordered_by_created()
    {
        $now   = Carbon::now();
        $user  = User::factory()->create();
        $post1 = Post::factory()->usingUser($user)->create([
            'created_at' => $now->clone()->subMinutes(20),
        ]);
        $post2 = Post::factory()->usingUser($user)->create([
            'created_at' => $now->clone()->subMinutes(40),
        ]);
        $post3 = Post::factory()->usingUser($user)->create([
            'created_at' => $now->clone()->subMinutes(30),
        ]);
        $post4 = Post::factory()->usingUser($user)->create([
            'created_at' => $now->clone()->subMinutes(10),
        ]);

        $this->login();
        $response = $this->get(URL::route($this->routeName, [$user]));

        $expectedData = Collection::make([
            $post4,
            $post1,
            $post3,
            $post2,
        ])->pluck(Post::routeKeyName());

        $response->assertStatus(Response::HTTP_OK);
        $data = Collection::make($response->json('data'));
        $this->assertEquals($expectedData, $data->pluck('id'));
    }

    /** @test */
    public function it_returns_a_list_of_posts_pinned_first()
    {
        $user        = User::factory()->create();
        $posts       = Post::factory()->usingUser($user)->count(10)->create();
        $pinnedPosts = Post::factory()->pinned()->usingUser($user)->count(2)->create();

        $this->login();
        $response = $this->get(URL::route($this->routeName, [$user]));

        $expectedData = $pinnedPosts->reverse()->merge($posts->reverse())->pluck(Post::routeKeyName());

        $response->assertStatus(Response::HTTP_OK);
        $data = Collection::make($response->json('data'));
        $this->assertEquals($expectedData, $data->pluck('id'));
    }

    /** @test */
    public function it_returns_a_list_of_posts_pinned_and_newest_first()
    {
        $user  = User::factory()->create();
        $posts = Post::factory()->usingUser($user)->count(20)->create();

        $solvedPost = $posts->get(1);
        Comment::factory()->usingPost($solvedPost)->solution()->create();

        $posts->add(Post::factory()->usingUser($user)->pinned()->create());

        $this->login();
        $response = $this->get(URL::route($this->routeName, [$user]));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertcount($response->json('meta.total'), $posts);

        $data           = Collection::make($response->json('data'));
        $firstPagePosts = $posts->reverse()->values()->take(count($data));

        $data->each(function(array $rawPost, int $index) use ($firstPagePosts) {
            $post = $firstPagePosts->get($index);
            $this->assertSame($post->getRouteKey(), $rawPost['id']);
            $this->assertSame($post->comments()->count(), $rawPost['total_comments']);
        });
    }
}
