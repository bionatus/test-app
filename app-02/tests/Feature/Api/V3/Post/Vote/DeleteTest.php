<?php

namespace Tests\Feature\Api\V3\Post\Vote;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\Post\VoteController;
use App\Models\Post;
use App\Models\PostVote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see VoteController */
class DeleteTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_POST_VOTE_DELETE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $post  = Post::factory()->create();
        $route = URL::route($this->routeName, [RouteParameters::POST => $post]);

        $this->expectException(UnauthorizedHttpException::class);

        $this->delete($route);
    }

    /** @test */
    public function it_delete_a_vote()
    {
        $postVote = PostVote::factory()->create();
        $route    = URL::route($this->routeName, [RouteParameters::POST => $postVote->post]);

        $this->login($postVote->user);

        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDeleted($postVote);
    }

    /** @test */
    public function it_ignores_the_request_if_the_user_never_voted_that_post()
    {
        $post = Post::factory()->create();
        PostVote::factory()->usingPost($post)->count(5)->create();
        $route = URL::route($this->routeName, [RouteParameters::POST => $post]);

        $this->login();

        $response = $this->delete($route);
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseCount(PostVote::tableName(), 5);
    }
}
