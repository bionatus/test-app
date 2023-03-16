<?php

namespace Tests\Feature\Api\V3\Post\Vote;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\Post\VoteController;
use App\Http\Resources\Api\V3\Post\BaseResource;
use App\Models\Post;
use App\Models\PostVote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see VoteController */
class StoreTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_POST_VOTE_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $post  = Post::factory()->create();
        $route = URL::route($this->routeName, [RouteParameters::POST => $post]);

        $this->expectException(UnauthorizedHttpException::class);

        $this->post($route);
    }

    /** @test */
    public function it_stores_a_vote()
    {
        $user  = User::factory()->create();
        $post  = Post::factory()->create();
        $route = URL::route($this->routeName, [RouteParameters::POST => $post]);

        $this->login($user);

        $response = $this->post($route);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals(1, $data->get('votes_count'));
        $this->assertCount(1, $post->votes);
        $this->assertEquals($user->getKey(), $post->votes->first()->user_id);
    }

    /** @test */
    public function it_does_not_increments_votes_if_already_voted()
    {
        $postVote = PostVote::factory()->create();
        $route    = URL::route($this->routeName, [RouteParameters::POST => $postVote->post]);

        $this->login($postVote->user);

        $response = $this->post($route);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals(1, $data->get('votes_count'));
        $this->assertCount(1, $postVote->post->votes);
        $this->assertEquals($postVote->user->getKey(), $postVote->post->votes->first()->user_id);
    }
}
