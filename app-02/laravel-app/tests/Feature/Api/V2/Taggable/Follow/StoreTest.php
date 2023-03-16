<?php

namespace Tests\Feature\Api\V2\Taggable\Follow;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Resources\Api\V2\Tag\DetailedResource;
use App\Models\PlainTag;
use App\Models\User;
use App\Models\UserTaggable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see FollowController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_TAGGABLE_FOLLOW_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName,
            [RouteParameters::TAGGABLE => PlainTag::factory()->create()->taggableRouteKey()]));
    }

    /** @test */
    public function it_stores_a_user_taggable()
    {
        $user  = User::factory()->create();
        $issue = PlainTag::factory()->issue()->create();

        $this->login($user);
        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::TAGGABLE => $issue->taggableRouteKey(),
        ]));
        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema(), false), $response);

        $this->assertCount(1, $user->followedTags);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($issue->getRouteKey(), $data->get('id'));
        $this->assertTrue($data->get('following'));
    }

    /** @test */
    public function it_answer_created_if_the_user_already_follows_the_tag()
    {
        $user  = User::factory()->create();
        $issue = PlainTag::factory()->issue()->create();

        UserTaggable::factory()->usingUser($user)->usingPlainTag($issue)->create();
        $this->login($user);
        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::TAGGABLE => $issue->taggableRouteKey(),
        ]));
        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema(), false), $response);

        $this->assertCount(1, $user->followedTags);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($issue->getRouteKey(), $data->get('id'));
        $this->assertTrue($data->get('following'));
    }
}
