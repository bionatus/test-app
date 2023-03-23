<?php

namespace Tests\Unit\Models\PlainTag;

use App\Models\PlainTag;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserTaggable;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property PlainTag $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = PlainTag::factory()->create();
    }

    /** @test */
    public function it_has_tags()
    {
        Tag::factory()->usingPlainTag($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->tags()->get();

        $this->assertCorrectRelation($related, Tag::class);
    }

    /** @test */
    public function it_belongs_to_many_posts()
    {
        Tag::factory()->usingPlainTag($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->posts()->get();

        $this->assertCorrectRelation($related, Post::class);
    }

    /** @test */
    public function it_has_userTaggables()
    {
        UserTaggable::factory()->usingPlainTag($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->userTaggables()->get();

        $this->assertCorrectRelation($related, UserTaggable::class);
    }

    /** @test */
    public function it_has_many_users_as_followers()
    {
        UserTaggable::factory()->usingPlainTag($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->followers()->get();

        $this->assertCorrectRelation($related, User::class);
    }
}
