<?php

namespace Tests\Feature\Api\V2\Post\Comment;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V2\Post\CommentController;
use App\Http\Resources\Api\V2\Post\Comment\BaseResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see CommentController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_POST_COMMENT_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName, Post::factory()->create()));
    }

    /** @test */
    public function it_display_a_list_of_comments()
    {
        $post     = Post::factory()->create();
        $comments = Comment::factory()->usingPost($post)->count(3)->create();

        $route = URL::route($this->routeName, $post);

        $this->login($post->user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertcount($response->json('meta.total'), $comments);

        $data           = Collection::make($response->json('data'));
        $firstPagePosts = $comments->take(count($data));

        $data->each(function(array $comment, int $index) use ($firstPagePosts) {
            $this->assertEquals($firstPagePosts->get($index)->getRouteKey(), $comment['id']);
        });
    }

    /** @test */
    public function it_prioritizes_the_comment_marked_as_solution()
    {
        $post = Post::factory()->create();
        Comment::factory()->usingPost($post)->count(2)->create();
        $solution = Comment::factory()->usingPost($post)->solution()->create();

        $route = URL::route($this->routeName, $post);

        $this->login($post->user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($solution->getRouteKey(), $data->first()['id']);
    }

    /** @test */
    public function it_only_shows_comments_created_before_the_specified_date()
    {
        $now = Carbon::now();

        $post     = Post::factory()->create([]);
        $comments = Comment::factory()->usingPost($post)->count(3)->create([
            'created_at' => $now->clone()->subMinutes(4),
        ]);
        Comment::factory()->usingPost($post)->create();

        $route = URL::route($this->routeName, $post);

        $this->login($post->user);

        $requestDate = $now->clone()->subSeconds(2);

        $response = $this->getWithParameters($route, [RequestKeys::CREATED_BEFORE => $requestDate->toIso8601String()]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertcount($response->json('meta.total'), $comments);

        $data           = Collection::make($response->json('data'));
        $firstPagePosts = $comments->take(count($data));

        $data->each(function(array $comment, int $index) use ($firstPagePosts) {
            $this->assertEquals($firstPagePosts->get($index)->getRouteKey(), $comment['id']);
        });
    }
}
