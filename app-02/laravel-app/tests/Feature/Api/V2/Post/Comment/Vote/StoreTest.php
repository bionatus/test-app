<?php

namespace Tests\Feature\Api\V2\Post\Comment\Vote;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Resources\Api\V2\Post\Comment\BaseResource;
use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see VoteController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_POST_COMMENT_VOTE_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $comment = Comment::factory()->create();
        $route   = URL::route($this->routeName,
            [RouteParameters::POST => $comment->post, RouteParameters::COMMENT => $comment]);

        $this->expectException(UnauthorizedHttpException::class);

        $this->post($route);
    }

    /** @test */
    public function it_stores_a_vote()
    {
        $user    = User::factory()->create();
        $comment = Comment::factory()->create();
        $route   = URL::route($this->routeName,
            [RouteParameters::POST => $comment->post, RouteParameters::COMMENT => $comment]);

        $this->login($user);

        $response = $this->post($route);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals(1, $data->get('votes_count'));
        $this->assertCount(1, $comment->votes);
        $this->assertEquals($user->getKey(), $comment->votes->first()->user_id);
    }

    /** @test */
    public function it_does_not_increments_votes_if_already_voted()
    {
        $commentVote = CommentVote::factory()->create();
        $route       = URL::route($this->routeName,
            [RouteParameters::POST => $commentVote->comment->post, RouteParameters::COMMENT => $commentVote->comment]);

        $this->login($commentVote->user);

        $response = $this->post($route);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals(1, $data->get('votes_count'));
        $this->assertCount(1, $commentVote->comment->votes);
        $this->assertEquals($commentVote->user->getKey(), $commentVote->comment->votes->first()->user_id);
    }
}
