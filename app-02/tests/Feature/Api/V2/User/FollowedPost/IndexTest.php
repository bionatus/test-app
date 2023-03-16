<?php

namespace Tests\Feature\Api\V2\User\FollowedPost;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V2\User\FollowedPostController;
use App\Http\Requests\Api\V2\User\FollowedPost\IndexRequest;
use App\Http\Resources\Api\V2\Post\BaseResource;
use App\Models\PlainTag;
use App\Models\Post;
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

/** @see FollowedPostController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_USER_FOLLOWED_POST_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, IndexRequest::class);
    }

    /** @test */
    public function it_display_a_list_of_my_followed_posts_newest_first()
    {
        $now   = Carbon::now();
        $post1 = Post::factory()->create(['created_at' => $now->clone()->subDays(4)]);
        $post2 = Post::factory()->create(['created_at' => $now->clone()->subDays(3)]);
        $post3 = Post::factory()->create(['created_at' => $now->clone()->subDays(2)]);
        $post4 = Post::factory()->create(['created_at' => $now->clone()->subDays(1)]);
        Post::factory()->count(20)->create();
        $post5 = Post::factory()->create();

        $tag1 = PlainTag::factory()->issue()->create();
        $tag2 = PlainTag::factory()->issue()->create();
        $tag3 = PlainTag::factory()->issue()->create();

        Tag::factory()->usingPost($post1)->usingPlainTag($tag1)->create();
        Tag::factory()->usingPost($post1)->usingPlainTag($tag3)->create();

        Tag::factory()->usingPost($post2)->usingPlainTag($tag1)->create();

        Tag::factory()->usingPost($post3)->usingPlainTag($tag2)->create();

        Tag::factory()->usingPost($post4)->usingPlainTag($tag1)->create();
        Tag::factory()->usingPost($post4)->usingPlainTag($tag2)->create();

        Tag::factory()->usingPost($post5)->usingPlainTag($tag3)->create();

        $user = User::factory()->create();

        UserTaggable::factory()->usingPlainTag($tag1)->usingUser($user)->create();
        UserTaggable::factory()->usingPlainTag($tag2)->usingUser($user)->create();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals(4, $response->json('meta.total'));

        $data          = Collection::make($response->json('data'));
        $expectedPosts = Collection::make([$post4, $post3, $post2, $post1]);

        $data->each(function(array $rawPost, int $index) use ($expectedPosts) {
            $post = $expectedPosts->get($index);
            $this->assertSame($post->getRouteKey(), $rawPost['id']);
        });
    }

    /** @test */
    public function it_display_an_empty_list_if_there_are_no_posts_with_a_followed_tag()
    {
        Post::factory()->count(20)->create();
        Tag::factory()->count(5)->create();

        $userTaggable = UserTaggable::factory()->create();

        $route = URL::route($this->routeName);

        $this->login($userTaggable->user);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals(0, $response->json('meta.total'));
    }

    /** @test */
    public function it_display_an_empty_list_if_the_user_does_not_follow_any_tag()
    {
        Tag::factory()->count(5)->create();

        $route = URL::route($this->routeName);

        $this->login();

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals(0, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_search_for_followed_posts_by_text()
    {
        $regularPost = Post::factory()->create(['message' => 'A regular post']);
        $posts       = Post::factory()->count(3)->create(['message' => 'A special post']);
        $tag         = PlainTag::factory()->issue()->create();
        Tag::factory()->usingPost($regularPost)->usingPlainTag($tag)->create();
        foreach ($posts as $post) {
            Tag::factory()->usingPost($post)->usingPlainTag($tag)->create();
        }

        $user = User::factory()->create();
        UserTaggable::factory()->usingPlainTag($tag)->usingUser($user)->create();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->getWithParameters($route, [RequestKeys::SEARCH_STRING => 'special']);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertSame($posts->count(), $response->json('meta.total'));

        $data           = Collection::make($response->json('data'));
        $firstPagePosts = $posts->reverse()->values()->take(count($data));

        $data->each(function(array $rawPost, int $index) use ($firstPagePosts) {
            $post = $firstPagePosts->get($index);
            $this->assertSame($post->getRouteKey(), $rawPost['id']);
        });
    }

    /** @test */
    public function validated_parameters_are_present_in_pagination_links()
    {
        $regularPost = Post::factory()->create(['message' => 'A regular post']);
        $tag         = PlainTag::factory()->issue()->create();
        Tag::factory()->usingPost($regularPost)->usingPlainTag($tag)->create();
        $user = User::factory()->create();
        UserTaggable::factory()->usingPlainTag($tag)->usingUser($user)->create();

        $this->login($user);

        $parameters = [RequestKeys::SEARCH_STRING => 'any string', 'invalid' => 'invalid'];

        $response = $this->get(URL::route($this->routeName, $parameters));
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $parsedUrl = parse_url($response->json('links.first'));
        parse_str($parsedUrl['query'], $queryString);

        $this->assertArrayHasKey(RequestKeys::SEARCH_STRING, $queryString);
        $this->assertArrayNotHasKey('invalid', $queryString);
    }

    /** @test */
    public function it_only_shows_post_created_before_the_specified_date()
    {
        $now        = Carbon::now();
        $oldPostOne = Post::factory()->create(['created_at' => $now->clone()->subDays(4)]);
        $oldPostTwo = Post::factory()->create(['created_at' => $now->clone()->subDays(3)]);
        $newPost    = Post::factory()->create();

        $tag1 = PlainTag::factory()->issue()->create();

        Tag::factory()->usingPost($oldPostOne)->usingPlainTag($tag1)->create();
        Tag::factory()->usingPost($oldPostTwo)->usingPlainTag($tag1)->create();
        Tag::factory()->usingPost($newPost)->usingPlainTag($tag1)->create();

        $user = User::factory()->create();

        UserTaggable::factory()->usingPlainTag($tag1)->usingUser($user)->create();

        $route = URL::route($this->routeName);

        $this->login($user);

        $requestDate = $now->clone()->subSeconds(2);

        $response = $this->getWithParameters($route, [RequestKeys::CREATED_BEFORE => $requestDate->toIso8601String()]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $data          = Collection::make($response->json('data'));
        $expectedPosts = Collection::make([$oldPostOne, $oldPostTwo]);
        $this->assertEquals($expectedPosts->count(), $response->json('meta.total'));

        $this->assertEqualsCanonicalizing($expectedPosts->pluck(Post::routeKeyName()), $data->pluck('id'));
    }
}
