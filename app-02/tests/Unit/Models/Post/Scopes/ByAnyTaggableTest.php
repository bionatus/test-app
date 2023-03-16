<?php

namespace Tests\Unit\Models\Post\Scopes;

use App\Models\Post;
use App\Models\Post\Scopes\ByAnyTaggable;
use App\Models\Tag;
use App\Types\TaggablesCollection;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByAnyTaggableTest extends TestCase
{
    use RefreshDatabase;

    private Tag $plainTagTypeGeneral;
    private Tag $seriesTag;
    private Tag $plainTagTypeMore;
    private Tag $plainTagTypeIssue;

    protected function setUp(): void
    {
        parent::setUp();

        Post::factory()->create();
        $plainTagTypeGeneral = Tag::factory()->general()->create();
        $seriesTag           = Tag::factory()->series()->create();
        $plainTagTypeMore    = Tag::factory()->more()->create();

        $firstPost = Post::factory()->create();
        Tag::factory()->usingSeries($seriesTag->taggable)->usingPost($firstPost)->create();
        Tag::factory()->usingPlainTag($plainTagTypeGeneral->taggable)->usingPost($firstPost)->create();

        $secondPost = Post::factory()->create();
        Tag::factory()->usingSeries($seriesTag->taggable)->usingPost($secondPost)->create();
        Tag::factory()->usingPlainTag($plainTagTypeMore->taggable)->usingPost($secondPost)->create();
        $this->plainTagTypeIssue = Tag::factory()->issue()->usingPost($secondPost)->create();

        $this->seriesTag           = $seriesTag;
        $this->plainTagTypeMore    = $plainTagTypeMore;
        $this->plainTagTypeGeneral = $plainTagTypeGeneral;
    }

    /** @test
     * @throws Exception
     */
    public function it_filters_by_one_tag()
    {
        $taggables = new TaggablesCollection([$this->plainTagTypeGeneral->taggable]);

        $posts = Post::scoped(new ByAnyTaggable($taggables))->get();

        $this->assertCount(2, $posts);
    }

    /** @test
     * @throws Exception
     */
    public function it_filters_by_multiple_different_tags()
    {
        $taggables = new TaggablesCollection([$this->plainTagTypeGeneral->taggable, $this->seriesTag->taggable]);
        $posts     = Post::scoped(new ByAnyTaggable($taggables))->get();

        $this->assertCount(4, $posts);
    }

    /** @test
     * @throws Exception
     */
    public function it_filters_by_multiple_same_type_tags()
    {
        $taggables = new TaggablesCollection([$this->plainTagTypeIssue->taggable, $this->plainTagTypeMore->taggable]);
        $posts     = Post::scoped(new ByAnyTaggable($taggables))->get();

        $this->assertCount(2, $posts);
    }

    /** @test */
    public function it_return_nothing_on_an_empty_collection()
    {
        $posts = Post::scoped(new ByAnyTaggable(new TaggablesCollection()))->get();

        $this->assertCount(0, $posts);
    }
}
