<?php

namespace Tests\Unit\Models\UserTaggable;

use App\Models\PlainTag;
use App\Models\Series;
use App\Models\User;
use App\Models\UserTaggable;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property UserTaggable $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = UserTaggable::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_has_a_taggable()
    {
        $plainTagTag = UserTaggable::factory()->plainTag()->create();
        $seriesTag   = UserTaggable::factory()->series()->create();

        $series   = $seriesTag->taggable()->first();
        $plainTag = $plainTagTag->taggable()->first();

        $this->assertInstanceOf(PlainTag::class, $plainTag);
        $this->assertInstanceOf(Series::class, $series);
    }
}
