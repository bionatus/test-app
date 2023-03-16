<?php

namespace Tests\Feature\Api\V2\Post\Comment;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Models\Comment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;
use Illuminate\Support\Facades\Bus;
use App\Jobs\LogActivity;

/** @see CommentController */
class DeleteTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V2_POST_COMMENT_DELETE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $comment = Comment::factory()->create();
        $route   = URL::route($this->routeName, [$comment->post, $comment]);

        $this->delete($route);
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:delete,' . RouteParameters::COMMENT]);
    }

    /** @test */
    public function it_deletes_a_comment()
    {
        $comment = Comment::factory()->create();
        $route   = URL::route($this->routeName, [
            RouteParameters::POST    => $comment->post,
            RouteParameters::COMMENT => $comment,
        ]);

        $this->login($comment->user);

        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDeleted($comment);
    }

    /** @test */
    public function it_should_not_delete_a_comment_that_does_not_belongs_to_a_post()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ModelNotFoundException::class);

        $comment1 = Comment::factory()->create();
        $comment2 = Comment::factory()->create();
        $route    = URL::route($this->routeName, ['post' => $comment1->post, 'comment' => $comment2]);

        $this->login($comment2->user);

        $this->delete($route);
    }

    /** @test */
    public function it_should_dispatch_an_activity_log_job()
    {
        Bus::fake();

        $comment = Comment::factory()->create();
        $route   = URL::route($this->routeName, [
            RouteParameters::POST    => $comment->post,
            RouteParameters::COMMENT => $comment,
        ]);

        $this->login($comment->user);
        $this->delete($route);

        Bus::assertDispatched(LogActivity::class);
    }
}
