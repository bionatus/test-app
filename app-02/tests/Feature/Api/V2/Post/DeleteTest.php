<?php

namespace Tests\Feature\Api\V2\Post;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V2\PostController;
use App\Jobs\LogActivity;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Bus;
use JMac\Testing\Traits\AdditionalAssertions;
use MohammedManssour\FormRequestTester\TestsFormRequests;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see PostController */
class DeleteTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use TestsFormRequests;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V2_POST_DELETE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $route = URL::route($this->routeName, Post::factory()->create());

        $this->delete($route);
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:delete,' . RouteParameters::POST]);
    }

    /** @test */
    public function it_deletes_a_post()
    {
        $this->withoutExceptionHandling();

        $post  = Post::factory()->create();
        $route = URL::route($this->routeName, $post);

        $this->login($post->user);

        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDeleted($post);
    }

    /** @test */
    public function it_should_dispatch_an_activity_log_job()
    {
        Bus::fake();

        $post  = Post::factory()->create();
        $route = URL::route($this->routeName, $post);

        $this->login($post->user);
        $this->delete($route);

        Bus::assertDispatched(LogActivity::class);
    }
}
