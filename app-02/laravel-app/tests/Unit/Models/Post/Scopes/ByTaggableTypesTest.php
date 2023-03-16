<?php

namespace Tests\Unit\Models\Post\Scopes;

use App\Models\IsTaggable;
use App\Models\ModelType;
use App\Models\PlainTag;
use App\Models\Post;
use App\Models\Series;
use App\Models\Tag;
use App\Types\TaggableType;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ByTaggableTypesTest extends TestCase
{
    use RefreshDatabase;

    private IsTaggable $series;
    private IsTaggable $more;
    private IsTaggable $system;
    private IsTaggable $issue;

    protected function setUp(): void
    {
        parent::setUp();

        Post::factory()->create();
        $series    = Series::factory()->create();
        $modelType = ModelType::factory()->create();
        $issue     = PlainTag::factory()->issue()->create();
        $tagMore   = PlainTag::factory()->more()->create();

        $firstPost = Post::factory()->create();
        Tag::factory()->usingPost($firstPost)->usingPlainTag($tagMore)->create();
        Tag::factory()->usingPost($firstPost)->usingSeries($series)->create();
        Tag::factory()->usingPost($firstPost)->usingPlainTag($issue)->create();

        $secondPost = Post::factory()->create();
        Tag::factory()->usingPost($secondPost)->usingModelType($modelType)->create();
        Tag::factory()->usingPost($secondPost)->usingPlainTag($issue)->create();

        $this->series = $series;
        $this->more   = $tagMore;
        $this->system = $modelType;
        $this->issue  = $issue;
    }

    /** @test
     * @throws Exception
     */
    public function it_filters_by_one_taggableType()
    {
        $taggableTypes = Collection::make([$this->more->toTagType()]);

        $posts = Post::scoped(new Post\Scopes\ByTaggableTypes($taggableTypes))->get();

        $this->assertCount(1, $posts);
    }

    /** @test
     * @throws Exception
     */
    public function it_filters_by_multiple_tags_connected_by_and()
    {
        $taggableTypes = Collection::make([
            $this->more->toTagType(),
            $this->series->toTagType(),
            $this->issue->toTagType(),
        ]);

        $posts = Post::scoped(new Post\Scopes\ByTaggableTypes($taggableTypes))->get();

        $this->assertCount(1, $posts);
    }

    /** @test
     * @throws Exception
     */
    public function it_filters_by_multiple_tags_connected_by_or()
    {
        $taggableTypes = Collection::make([
            new TaggableType([
                'id'        => $this->more->getRouteKey(),
                'type'      => $this->more->morphType(),
                'connector' => TaggableType::CONNECTOR_OR,
            ]),
            new TaggableType([
                'id'        => $this->system->getRouteKey(),
                'type'      => $this->system->morphType(),
                'connector' => TaggableType::CONNECTOR_OR,
            ]),
        ]);

        $posts = Post::scoped(new Post\Scopes\ByTaggableTypes($taggableTypes))->get();

        $this->assertCount(2, $posts);
    }

    /** @test
     * @throws Exception
     */
    public function it_filters_by_multiple_tags_connected_by_both_connectors()
    {
        $taggableTypes = Collection::make([
            $this->more->toTagType(),
            $this->series->toTagType(),
            new TaggableType([
                'id'        => $this->system->getRouteKey(),
                'type'      => $this->system->morphType(),
                'connector' => TaggableType::CONNECTOR_OR,
            ]),
        ]);

        $posts = Post::scoped(new Post\Scopes\ByTaggableTypes($taggableTypes))->get();

        $this->assertCount(2, $posts);
    }

    /** @test
     * @throws Exception
     */
    public function it_filters_by_multiple_connectors_using_and_first()
    {
        $taggableTypes = Collection::make([
            $this->more->toTagType(),
            new TaggableType([
                'id'        => $this->system->getRouteKey(),
                'type'      => $this->system->morphType(),
                'connector' => TaggableType::CONNECTOR_OR,
            ]),
            $this->series->toTagType(),
        ]);

        $posts = Post::scoped(new Post\Scopes\ByTaggableTypes($taggableTypes))->get();

        $this->assertCount(2, $posts);
    }

    /** @test */
    public function it_filters_nothing_on_an_empty_collection()
    {
        $taggableTypes = Collection::make();

        $posts = Post::scoped(new Post\Scopes\ByTaggableTypes($taggableTypes))->get();
        $this->assertCount(3, $posts);
    }
}
