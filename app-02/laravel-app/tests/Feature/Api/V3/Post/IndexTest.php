<?php

namespace Tests\Feature\Api\V3\Post;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\PostController;
use App\Http\Requests\Api\V2\Post\IndexRequest;
use App\Http\Resources\Api\V3\Post\BaseResource;
use App\Models\Comment;
use App\Models\ModelType;
use App\Models\PlainTag;
use App\Models\Post;
use App\Models\Series;
use App\Models\Tag;
use App\Types\TaggableType;
use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see PostController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_POST_INDEX;

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
    public function it_display_a_list_of_posts_pinned_and_newest_first()
    {
        $posts = Post::factory()->count(100)->create();
        $route = URL::route($this->routeName);

        $solvedPost = $posts->get(1);
        Comment::factory()->usingPost($solvedPost)->solution()->create();

        $posts->add(Post::factory()->pinned()->create());

        $this->login();
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $posts);

        $data           = Collection::make($response->json('data'));
        $firstPagePosts = $posts->reverse()->values()->take(count($data));

        $data->each(function(array $rawPost, int $index) use ($firstPagePosts) {
            $post = $firstPagePosts->get($index);
            $this->assertSame($post->getRouteKey(), $rawPost['id']);
            $this->assertSame($post->comments()->count(), $rawPost['total_comments']);
        });
    }

    /** @test */
    public function it_can_search_for_posts_by_text()
    {
        Post::factory()->count(2)->create(['message' => 'A regular post']);
        $posts = Post::factory()->count(3)->create(['message' => 'A special post']);
        $route = URL::route($this->routeName);

        $this->login();
        $response = $this->getWithParameters($route, [RequestKeys::SEARCH_STRING => 'special']);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertSame($posts->count(), $response->json('meta.total'));

        $data           = Collection::make($response->json('data'));
        $firstPagePosts = $posts->reverse()->values()->take(count($data));

        $data->each(function(array $rawPost, int $index) use ($firstPagePosts) {
            $post = $firstPagePosts->get($index);
            $this->assertSame($post->getRouteKey(), $rawPost['id']);
            $this->assertSame($post->comments()->count(), $rawPost['total_comments']);
        });
    }

    /** @test
     * @throws Exception
     */
    public function it_can_search_for_posts_by_tags()
    {
        $seriesTag = Tag::factory()->series()->create();
        Tag::factory()->general()->create();
        Tag::factory()->issue()->create();
        $moreTag = Tag::factory()->more()->create();

        $firstPost  = Post::factory()->create();
        $secondPost = Post::factory()->create();
        Tag::factory()->usingPost($firstPost)->usingSeries($seriesTag->taggable)->create();
        Tag::factory()->usingPost($secondPost)->usingPlainTag($moreTag->taggable)->create();
        Tag::factory()->usingPost($secondPost)->usingSeries($seriesTag->taggable)->create();

        $route = URL::route($this->routeName);
        $this->login();

        /** @var Series $series */
        $series = $seriesTag->taggable;
        /** @var PlainTag $more */
        $more = $moreTag->taggable;

        $parameters = [
            RequestKeys::TAGS => [
                $series->toTagType()->toArray(),
                $more->toTagType()->toArray(),
            ],
        ];

        $response = $this->getWithParameters($route, $parameters);
        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals(1, $response->json('meta.total'));

        $data              = Collection::make($response->json('data'));
        $firstResponsePost = $data->first();
        $this->assertEquals($firstResponsePost['id'], $secondPost->getRouteKey());
    }

    /** @test
     * @throws Exception
     */
    public function validated_parameters_are_present_in_pagination_links()
    {
        $this->login();

        $taggable   = Series::factory()->create();
        $tag        = $taggable->toTagType();
        $parameters = [RequestKeys::SEARCH_STRING => 'any string', RequestKeys::TAGS => [$tag], 'invalid' => 'invalid'];

        $response = $this->get(URL::route($this->routeName, $parameters));
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $parsedUrl = parse_url($response->json('links.first'));
        parse_str($parsedUrl['query'], $queryString);
        $tagArray = $tag->toArray();

        $this->assertArrayHasKey(RequestKeys::SEARCH_STRING, $queryString);
        $this->assertArrayHasKey(RequestKeys::TAGS, $queryString);
        $this->assertArrayHasKeysAndValues([$tagArray], $queryString[RequestKeys::TAGS]);
        $this->assertArrayNotHasKey('invalid', $queryString);
    }

    /** @test
     * @throws Exception
     */
    public function it_includes_posts_containing_a_tag_with_an_or_connector()
    {
        $firstPost  = Post::factory()->create();
        $secondPost = Post::factory()->create();
        Post::factory()->count(10)->create();

        $series = Series::factory()->create();
        Tag::factory()->usingPost($firstPost)->usingSeries($series)->create();
        Tag::factory()->usingPost($firstPost)->issue()->create();
        $modelType = ModelType::factory()->create();
        Tag::factory()->usingPost($secondPost)->usingModelType($modelType)->create();
        Tag::factory()->usingPost($secondPost)->issue()->create();

        $route = URL::route($this->routeName);
        $this->login();

        $parameters = [
            RequestKeys::TAGS => [
                $series->toTagType()->toArray(),
                [
                    'id'        => $modelType->getRouteKey(),
                    'type'      => $modelType->morphType(),
                    'connector' => TaggableType::CONNECTOR_OR,
                ],
            ],
        ];

        $response = $this->getWithParameters($route, $parameters);
        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals(2, $response->json('meta.total'));
    }

    /** @test
     * @throws Exception
     */
    public function it_prioritizes_posts_by_requested_tags_quantity()
    {
        $post1     = Post::factory()->create();
        $post2     = Post::factory()->create();
        $post3     = Post::factory()->create();
        $series    = Series::factory()->create();
        $modelType = ModelType::factory()->create();
        $issue     = PlainTag::factory()->issue()->create();

        Tag::factory()->usingPost($post2)->usingPlainTag($issue)->create();
        Tag::factory()->usingPost($post2)->usingSeries($series)->create();
        Tag::factory()->usingPost($post2)->usingModelType($modelType)->create();

        Tag::factory()->usingPost($post3)->usingPlainTag($issue)->create();
        Tag::factory()->usingPost($post3)->usingSeries($series)->create();

        Tag::factory()->usingPost($post1)->usingModelType($modelType)->create();

        Post::factory()->count(100)->create();
        $route = URL::route($this->routeName);

        $this->login();

        $parameters = [
            RequestKeys::TAGS => [
                $issue->toTagType()->toArray(),
                $series->toTagType()->toArray(),
                [
                    'id'        => $modelType->getRouteKey(),
                    'type'      => $modelType->morphType(),
                    'connector' => TaggableType::CONNECTOR_OR,
                ],
            ],
        ];

        $response = $this->getWithParameters($route, $parameters);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals(3, $response->json('meta.total'));

        $data = Collection::make($response->json('data'));

        $this->assertEquals($post2->uuid, $data[0]['id']);
        $this->assertEquals($post3->uuid, $data[1]['id']);
        $this->assertEquals($post1->uuid, $data[2]['id']);
    }

    /** @test
     * @throws Exception
     */
    public function it_ignores_tags_where_the_model_was_deleted()
    {
        $post  = Post::factory()->create();
        $issue = PlainTag::factory()->issue()->create();

        Tag::factory()->usingPost($post)->usingPlainTag($issue)->create();
        Tag::factory()->usingPost($post)->create([
            'taggable_id'   => 5,
            'taggable_type' => Relation::getAliasByModel(Series::class),
        ]);

        $route = URL::route($this->routeName);

        $this->login();

        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals(1, $response->json('meta.total'));

        $data      = $response->json('data');
        $firstPost = $data[0];

        $this->assertArrayHasKey('tags', $firstPost);
        $this->assertIsArray($firstPost['tags']);
        $this->assertArrayHasKey('data', $firstPost['tags']);
        $this->assertIsArray($firstPost['tags']['data']);
        $this->assertCount(1, $firstPost['tags']['data']);

        $tag = $firstPost['tags']['data'][0];
        $this->assertEquals($issue->toTagType()->toArray(), $tag);
    }

    /** @test */
    public function it_only_shows_post_created_before_the_specified_date()
    {
        $now      = Carbon::now();
        $oldPosts = Post::factory()->count(5)->create(['created_at' => $now->clone()->subSeconds(10)]);
        Post::factory()->create();
        $route = URL::route($this->routeName);

        $this->login();

        $requestDate = $now->clone()->subSeconds(2);

        $response = $this->getWithParameters($route, [RequestKeys::CREATED_BEFORE => $requestDate->toIso8601String()]);
        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertSame($oldPosts->count(), $response->json('meta.total'));

        $data           = Collection::make($response->json('data'));
        $firstPagePosts = $oldPosts->reverse()->values()->take(count($data));

        $data->each(function(array $rawPost, int $index) use ($firstPagePosts) {
            $post = $firstPagePosts->get($index);
            $this->assertSame($post->getRouteKey(), $rawPost['id']);
        });
    }

    /** @test */
    public function it_can_filter_for_posts_by_type()
    {
        $posts = Post::factory()->count(3)->needsHelp()->create();
        Post::factory()->count(2)->create();
        $route = URL::route($this->routeName);

        $this->login();
        $response = $this->getWithParameters($route, [RequestKeys::TYPE => Post::TYPE_NEEDS_HELP]);
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
}
