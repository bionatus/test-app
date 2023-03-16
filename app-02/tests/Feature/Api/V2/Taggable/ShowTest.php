<?php

namespace Tests\Feature\Api\V2\Taggable;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V2\TaggableController;
use App\Http\Resources\Api\V2\Tag\DetailedResource;
use App\Models\Comment;
use App\Models\Media;
use App\Models\PlainTag;
use App\Models\Post;
use App\Models\Series;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserTaggable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see TaggableController */
class ShowTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_TAGGABLE_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName, [PlainTag::factory()->create()->taggableRouteKey()]));
    }

    /** @test */
    public function it_display_a_plain_tag_taggable()
    {
        $user     = User::factory()->create();
        $plainTag = PlainTag::factory()->create();
        $media    = Media::factory()->usingTag($plainTag)->create();

        $route = URL::route($this->routeName, $plainTag->taggableRouteKey());

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($data['id'], $plainTag->getRouteKey());
        $this->assertEquals($data['type'], $plainTag->morphType());
        $images = $data['images']['data'];
        $this->assertCount(1, $images);
        $this->assertEquals($media->uuid, $images[0]['id']);
    }

    /** @test */
    public function it_display_a_series_tag_taggable()
    {
        $user   = User::factory()->create();
        $series = Series::factory()->create();

        $route = URL::route($this->routeName, $series->taggableRouteKey());

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($data['id'], $series->getRouteKey());
        $this->assertEquals($data['type'], $series->morphType());

        $images = $data['images']['data'];
        $this->assertCount(1, $images);

        $this->assertEquals($series->image, $images[0]['id']);
        $this->assertEquals($series->image, $images[0]['url']);
    }

    /** @test */
    public function it_shows_if_the_user_is_following_the_tag()
    {
        $user     = User::factory()->create();
        $plainTag = PlainTag::factory()->create();

        UserTaggable::factory()->usingUser($user)->usingPlainTag($plainTag)->create();

        $route = URL::route($this->routeName, $plainTag->taggableRouteKey());

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($data['id'], $plainTag->getRouteKey());
        $this->assertEquals($data['type'], $plainTag->morphType());
        $this->assertTrue($data['following']);
    }

    /** @test */
    public function it_shows_the_count_of_posts_that_contains_the_tag()
    {
        $user     = User::factory()->create();
        $plainTag = PlainTag::factory()->create();
        Tag::factory()->usingPlainTag($plainTag)->count(10)->create();

        $route = URL::route($this->routeName, $plainTag->taggableRouteKey());

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($data['id'], $plainTag->getRouteKey());
        $this->assertEquals($data['type'], $plainTag->morphType());
        $this->assertSame(10, $data['posts_count']);
    }

    /** @test */
    public function it_shows_the_count_of_users_that_follows_the_tag()
    {
        $user     = User::factory()->create();
        $plainTag = PlainTag::factory()->create();
        UserTaggable::factory()->usingPlainTag($plainTag)->count(10)->create();

        $route = URL::route($this->routeName, $plainTag->taggableRouteKey());

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($data['id'], $plainTag->getRouteKey());
        $this->assertEquals($data['type'], $plainTag->morphType());
        $this->assertSame(10, $data['followers_count']);
    }

    /** @test */
    public function it_display_the_first_page_of_posts_that_contains_the_tag_newest_first()
    {
        $user     = User::factory()->create();
        $plainTag = PlainTag::factory()->create();

        $oldPost = Post::factory()->create(['created_at' => Carbon::now()->subDay()]);
        Tag::factory()->usingPlainTag($plainTag)->usingPost($oldPost)->create();
        Comment::factory()->usingPost($oldPost)->solution()->create();
        Tag::factory()->usingPlainTag($plainTag)->count(50)->create();

        $route = URL::route($this->routeName, $plainTag->taggableRouteKey());

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($data['id'], $plainTag->getRouteKey());
        $this->assertEquals($data['type'], $plainTag->morphType());
        $this->assertSame(51, $data['posts_count']);

        $dataPosts = Collection::make($data['posts']['data'] ?? []);

        $firstPagePosts = $plainTag->posts->sortByDesc(Post::CREATED_AT)
            ->sortByDesc(Post::keyName())
            ->take($dataPosts->count());

        $this->assertEquals($firstPagePosts->pluck(Post::routeKeyName())->toArray(),
            $dataPosts->pluck('id')->toArray());
    }
}
