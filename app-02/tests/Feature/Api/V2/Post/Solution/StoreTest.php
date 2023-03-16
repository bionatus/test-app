<?php

namespace Tests\Feature\Api\V2\Post\Solution;

use App;
use App\Actions\Models\SettingUser\GetNotificationSetting;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\Post\Solution\Created;
use App\Http\Resources\Api\V2\Post\Comment\BaseResource;
use App\Jobs\LogActivity;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Mockery;
use MohammedManssour\FormRequestTester\TestsFormRequests;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see SolutionController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use TestsFormRequests;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_POST_SOLUTION_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $post  = Post::factory()->create();
        $route = URL::route($this->routeName, ['post' => $post]);

        $this->expectException(UnauthorizedHttpException::class);

        $this->post($route);
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:solve,' . RouteParameters::POST]);
    }

    /** @test */
    public function it_stores_a_solution()
    {
        $comment = Comment::factory()->create();
        $post    = $comment->post;
        $route   = URL::route($this->routeName, ['post' => $post]);

        $action = Mockery::mock(GetNotificationSetting::class);
        $action->shouldReceive('execute')->withNoArgs()->andReturn(true);
        App::bind(GetNotificationSetting::class, fn() => $action);

        $this->login($post->user);
        $response = $this->post($route, [RequestKeys::SOLUTION => $comment->getRouteKey()]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertNotNull($data->get('solution'));
    }

    /** @test */
    public function it_replace_old_solution_with_the_provided_one()
    {
        $post = Post::factory()->create();
        Comment::factory()->usingPost($post)->count(2)->create();
        $currentSolution = Comment::factory()->usingPost($post)->solution()->create();
        $newSolution     = Comment::factory()->usingPost($post)->create();
        $action          = Mockery::mock(GetNotificationSetting::class);
        $action->shouldReceive('execute')->withNoArgs()->andReturn(true);
        App::bind(GetNotificationSetting::class, fn() => $action);

        $route = URL::route($this->routeName, ['post' => $post]);

        $this->login($post->user);
        $response = $this->post($route, [RequestKeys::SOLUTION => $newSolution->getRouteKey()]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($newSolution->getRouteKey(), $data->get('id'));
        $this->assertNotNull($data->get('solution'));

        $this->assertFalse($currentSolution->fresh()->isSolution());
        $this->assertTrue($newSolution->fresh()->isSolution());
    }

    /** @test */
    public function it_should_dispatch_an_activity_log_job()
    {
        Bus::fake();

        $comment = Comment::factory()->create();
        $post    = $comment->post;
        $route   = URL::route($this->routeName, [RouteParameters::POST => $post]);

        $this->login($post->user);
        $this->post($route, [RequestKeys::SOLUTION => $comment->getRouteKey()]);

        Bus::assertDispatched(LogActivity::class);
    }

    /** @test */
    public function it_dispatches_a_solution_created_event()
    {
        Event::fake([Created::class]);

        $comment = Comment::factory()->create();
        $post    = $comment->post;
        $route   = URL::route($this->routeName, [RouteParameters::POST => $post]);

        $this->login($post->user);
        $this->post($route, [RequestKeys::SOLUTION => $comment->getRouteKey()]);

        Event::assertDispatched(Created::class);
    }
}
