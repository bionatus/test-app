<?php

namespace Tests\Unit\Models\ModelType;

use App\Models\ModelType;
use App\Models\Oem;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserTaggable;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property ModelType $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = ModelType::factory()->create();
    }

    /** @test */
    public function it_has_oems()
    {
        Oem::factory()->usingModelType($this->instance)->count(10)->create();

        $related = $this->instance->oems()->get();

        $this->assertCorrectRelation($related, Oem::class);
    }

    /** @test */
    public function it_has_tags()
    {
        Tag::factory()->usingModelType($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->tags()->get();

        $this->assertCorrectRelation($related, Tag::class);
    }

    /** @test */
    public function it_belongs_to_many_posts()
    {
        Tag::factory()->usingModelType($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->posts()->get();

        $this->assertCorrectRelation($related, Post::class);
    }

    /** @test */
    public function it_has_userTaggables()
    {
        UserTaggable::factory()->usingModelType($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->userTaggables()->get();

        $this->assertCorrectRelation($related, UserTaggable::class);
    }

    /** @test */
    public function it_has_many_users_as_followers()
    {
        UserTaggable::factory()->usingModelType($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->followers()->get();

        $this->assertCorrectRelation($related, User::class);
    }
}
