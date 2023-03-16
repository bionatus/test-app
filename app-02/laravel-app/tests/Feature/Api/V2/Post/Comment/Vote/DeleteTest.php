<?php

namespace Tests\Feature\Api\V2\Post\Comment\Vote;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Models\Comment;
use App\Models\CommentVote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see VoteController */
class DeleteTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_POST_COMMENT_VOTE_DELETE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $comment = Comment::factory()->create();
        $route   = URL::route($this->routeName,
            [RouteParameters::POST => $comment->post, RouteParameters::COMMENT => $comment]);

        $this->expectException(UnauthorizedHttpException::class);

        $this->delete($route);
    }

    /** @test */
    public function it_delete_a_vote()
    {
        $commentVote = CommentVote::factory()->create();
        $route       = URL::route($this->routeName,
            [RouteParameters::POST => $commentVote->comment->post, RouteParameters::COMMENT => $commentVote->comment]);

        $this->login($commentVote->user);

        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDeleted($commentVote);
    }

    /** @test */
    public function it_ignores_the_request_if_the_user_never_voted_that_comment()
    {
        $comment = Comment::factory()->create();
        CommentVote::factory()->usingComment($comment)->count(5)->create();
        $route = URL::route($this->routeName,
            [RouteParameters::POST => $comment->post, RouteParameters::COMMENT => $comment]);

        $this->login();

        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseCount(CommentVote::tableName(), 5);
    }
}
