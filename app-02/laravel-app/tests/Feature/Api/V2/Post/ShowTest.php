<?php

namespace Tests\Feature\Api\V2\Post;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V2\PostController;
use App\Http\Resources\Api\V2\Post\DetailedResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see PostController */
class ShowTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_POST_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName, Post::factory()->create()));
    }

    /** @test */
    public function it_display_a_post_with_the_first_page_of_its_comments_oldest_first()
    {
        $post     = Post::factory()->create();
        $comments = Comment::factory()->usingPost($post)->count(100)->create();
        $route    = URL::route($this->routeName, $post);

        $this->login($post->user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($data['id'], $post->getRouteKey());
        $this->assertSame($post->comments()->count(), $data['total_comments']);

        $dataComments      = Collection::make($data['comments']['data'] ?? []);
        $firstPageComments = $comments->take(count($dataComments));
        $dataComments->each(function (array $rawComment, int $index) use ($firstPageComments) {
            $comment = $firstPageComments->get($index);
            $this->assertEquals($comment->getRouteKey(), $rawComment['id']);
        });
    }
}
