<?php

namespace Tests\Feature\Api\V3\Post\Pin;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Resources\Api\V3\Post\BaseResource;
use App\Models\Post;
use App\Models\Post\Scopes\ByPinned;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see PinController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_POST_PIN_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName, [RouteParameters::POST => Post::factory()->create()->getRouteKey()]));
    }

    /** @test */
    public function it_sets_a_post_as_pinned()
    {
        $user = User::factory()->create(['email' => 'acurry@bionatusllc.com']);
        $post = Post::factory()->create();

        $this->login($user);
        $response = $this->post(URL::route($this->routeName, [RouteParameters::POST => $post->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(Post::tableName(), [
            'id'     => $post->getKey(),
            'pinned' => true,
        ]);
    }

    /** @test */
    public function it_unsets_the_pin_in_previously_pinned_posts()
    {
        $user = User::factory()->create(['email' => 'acurry@bionatusllc.com']);
        Post::factory()->pinned()->count(2)->create();
        $post = Post::factory()->create();

        $this->login($user);
        $response = $this->post(URL::route($this->routeName, [RouteParameters::POST => $post->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $this->assertSame(1, Post::scoped(new ByPinned(true))->count());
    }
}
