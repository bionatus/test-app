<?php

namespace Tests\Unit\Models\Post;

use App\Models\Brand;
use App\Models\Comment;
use App\Models\ModelType;
use App\Models\PlainTag;
use App\Models\Post;
use App\Models\PostVote;
use App\Models\Series;
use App\Models\Tag;
use App\Models\User;
use Auth;
use Illuminate\Database\Eloquent\Relations\Relation;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Post $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Post::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_has_comments()
    {
        Comment::factory()->usingPost($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->comments()->get();

        $this->assertCorrectRelation($related, Comment::class);
    }

    /** @test */
    public function it_has_tags()
    {
        Tag::factory()->usingPost($this->instance)->count(10)->create();

        $related = $this->instance->tags()->get();

        $this->assertCorrectRelation($related, Tag::class);
    }

    /** @test */
    public function it_ignores_tags_where_the_model_does_not_exists()
    {
        Tag::factory()->usingPost($this->instance)->count(self::COUNT)->create();

        Tag::factory()->usingPost($this->instance)->create([
            'taggable_type' => Relation::getAliasByModel(Series::class),
            'taggable_id'   => 0,
        ]);
        Tag::factory()->usingPost($this->instance)->create([
            'taggable_type' => Relation::getAliasByModel(Brand::class),
            'taggable_id'   => 0,
        ]);
        Tag::factory()->usingPost($this->instance)->create([
            'taggable_type' => Relation::getAliasByModel(PlainTag::class),
            'taggable_id'   => 0,
        ]);
        Tag::factory()->usingPost($this->instance)->create([
            'taggable_type' => Relation::getAliasByModel(ModelType::class),
            'taggable_id'   => 0,
        ]);

        $related = $this->instance->tags()->get();

        $this->assertCorrectRelation($related, Tag::class);
    }

    /** @test */
    public function it_has_series_as_tags()
    {
        Tag::factory()->usingPost($this->instance)->series()->count(10)->create();

        $related = $this->instance->tagSeries()->get();

        $this->assertCorrectRelation($related, Series::class);
    }

    /** @test */
    public function it_has_plain_tags_as_tags()
    {
        Tag::factory()->usingPost($this->instance)->plainTag()->count(10)->create();

        $related = $this->instance->tagPlainTags()->get();

        $this->assertCorrectRelation($related, PlainTag::class);
    }

    /** @test */
    public function it_has_a_solution_comment()
    {
        Comment::factory()->usingPost($this->instance)->count(2)->create();
        $solution = Comment::factory()->usingPost($this->instance)->solution()->create();
        Comment::factory()->usingPost($this->instance)->count(3)->create();

        $related = $this->instance->solutionComment()->first();

        $this->assertInstanceOf(Comment::class, $related);
        $this->assertEquals($solution->getKey(), $related->getKey());
    }

    /** @test */
    public function it_has_votes()
    {
        PostVote::factory()->usingPost($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->votes()->get();

        $this->assertCorrectRelation($related, PostVote::class);
    }

    /** @test */
    public function it_has_an_auth_user_vote()
    {
        $post     = Post::factory()->create();
        $postVote = PostVote::factory()->usingPost($post)->create();
        Auth::shouldReceive('user')->andReturn($postVote->user);
        $related = $post->authUserVote()->first();

        $this->assertInstanceOf(PostVote::class, $related);
        $this->assertEquals($postVote->getKey(), $related->getKey());
    }
}
