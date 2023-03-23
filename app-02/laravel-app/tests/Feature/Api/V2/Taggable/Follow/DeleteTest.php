<?php

namespace Tests\Feature\Api\V2\Taggable\Follow;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V2\TaggableController;
use App\Models\IsTaggable;
use App\Models\PlainTag;
use App\Models\User;
use App\Models\UserTaggable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see TaggableController */
class DeleteTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_TAGGABLE_FOLLOW_DELETE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->delete(URL::route($this->routeName,
            [RouteParameters::TAGGABLE => PlainTag::factory()->create()->taggableRouteKey()]));
    }

    /** @test */
    public function it_deletes_a_user_taggable()
    {
        $user         = User::factory()->create();
        $userTaggable = UserTaggable::factory()->usingUser($user)->create();

        /** @var IsTaggable $taggable */
        $taggable = $userTaggable->taggable;

        $this->login($user);
        $route    = URL::route($this->routeName, [RouteParameters::TAGGABLE => $taggable->taggableRouteKey()]);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDeleted($userTaggable);
    }

    /** @test */
    public function it_responds_no_content_if_the_user_does_not_follows_the_tag()
    {
        $user  = User::factory()->create();
        $issue = PlainTag::factory()->issue()->create();

        $this->login($user);
        $route    = URL::route($this->routeName, [RouteParameters::TAGGABLE => $issue->taggableRouteKey()]);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }
}
