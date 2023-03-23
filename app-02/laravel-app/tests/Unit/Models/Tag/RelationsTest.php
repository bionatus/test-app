<?php

namespace Tests\Unit\Models\Tag;

use App\Models\ModelType;
use App\Models\PlainTag;
use App\Models\Post;
use App\Models\Series;
use App\Models\System;
use App\Models\Tag;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Tag $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Tag::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_post()
    {
        $related = $this->instance->post()->first();

        $this->assertInstanceOf(Post::class, $related);
    }

    /** @test */
    public function it_has_a_taggable()
    {
        $plainTagTag  = Tag::factory()->plainTag()->create();
        $seriesTag    = Tag::factory()->series()->create();
        $modelTypeTag = Tag::factory()->modelType()->create();

        $series    = $seriesTag->taggable()->first();
        $plainTag  = $plainTagTag->taggable()->first();
        $modelType = $modelTypeTag->taggable()->first();

        $this->assertInstanceOf(PlainTag::class, $plainTag);
        $this->assertInstanceOf(Series::class, $series);
        $this->assertInstanceOf(ModelType::class, $modelType);
    }
}
