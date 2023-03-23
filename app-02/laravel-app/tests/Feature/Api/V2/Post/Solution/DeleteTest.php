<?php

namespace Tests\Feature\Api\V2\Post\Solution;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see SolutionController */
class DeleteTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V2_POST_SOLUTION_DELETE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $comment = Comment::factory()->create();
        $route   = URL::route($this->routeName, ['post' => $comment->post, 'comment' => $comment]);

        $this->delete($route);
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:unSolve,' . RouteParameters::POST]);
    }

    /** @test */
    public function it_deletes_a_solution()
    {
        $solution = Comment::factory()->solution()->create();
        $route    = URL::route($this->routeName, ['post' => $solution->post, 'comment' => $solution]);

        $this->login($solution->post->user);

        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertNull($solution->fresh()->solution);
    }

    /** @test */
    public function it_should_not_delete_a_solution_that_does_not_belongs_to_a_post()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ModelNotFoundException::class);

        $post     = Post::factory()->create();
        $solution = Comment::factory()->create();
        $route    = URL::route($this->routeName, ['post' => $post, 'comment' => $solution]);

        $this->login($post->user);

        $this->delete($route);
    }
}
