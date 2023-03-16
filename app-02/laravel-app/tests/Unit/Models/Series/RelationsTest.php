<?php

namespace Tests\Unit\Models\Series;

use App\Models\Brand;
use App\Models\Oem;
use App\Models\Post;
use App\Models\Product;
use App\Models\Series;
use App\Models\SeriesUser;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserTaggable;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Series $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Series::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_brand()
    {
        $related = $this->instance->brand()->first();

        $this->assertInstanceOf(Brand::class, $related);
    }

    /** @test */
    public function it_has_tags()
    {
        Tag::factory()->usingSeries($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->tags()->get();

        $this->assertCorrectRelation($related, Tag::class);
    }

    /** @test */
    public function it_belongs_to_many_posts()
    {
        Tag::factory()->usingSeries($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->posts()->get();

        $this->assertCorrectRelation($related, Post::class);
    }

    /** @test */
    public function it_has_userTaggables()
    {
        UserTaggable::factory()->usingSeries($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->userTaggables()->get();

        $this->assertCorrectRelation($related, UserTaggable::class);
    }

    /** @test */
    public function it_has_many_users_as_followers()
    {
        UserTaggable::factory()->usingSeries($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->followers()->get();

        $this->assertCorrectRelation($related, User::class);
    }

    /** @test */
    public function it_has_series_users()
    {
        SeriesUser::factory()->usingSeries($this->instance)->count(10)->create();

        $related = $this->instance->seriesUsers()->get();

        $this->assertCorrectRelation($related, SeriesUser::class);
    }

    /** @test */
    public function it_has_products()
    {
        Product::factory()->usingSeries($this->instance)->count(10)->create();

        $related = $this->instance->products()->get();

        $this->assertCorrectRelation($related, Product::class);
    }

    /** @test */
    public function it_has_users()
    {
        SeriesUser::factory()->usingSeries($this->instance)->count(10)->create();

        $related = $this->instance->users()->get();

        $this->assertCorrectRelation($related, User::class);
    }

    /** @test */
    public function it_has_oems()
    {
        Oem::factory()->usingSeries($this->instance)->count(10)->create();

        $related = $this->instance->oems()->get();

        $this->assertCorrectRelation($related, Oem::class);
    }
}
