<?php

namespace Tests\Feature\Api\V3\Post\Pin;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see PinController */
class DeleteTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_POST_PIN_DELETE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->delete(URL::route($this->routeName,
            [RouteParameters::POST => Post::factory()->create()->getRouteKey()]));
    }

    /** @test */
    public function it_sets_a_post_as_unpinned()
    {
        $user = User::factory()->create(['email' => 'acurry@bionatusllc.com']);
        $post = Post::factory()->usingUser($user)->create();

        $this->login($user);
        $response = $this->delete(URL::route($this->routeName, [RouteParameters::POST => $post->getRouteKey()]));

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseHas(Post::tableName(), [
            'id'     => $post->getKey(),
            'pinned' => false,
        ]);
    }
}
