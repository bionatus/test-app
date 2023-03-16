<?php

namespace Tests\Unit\Models\Tag\Scopes;

use App\Models\PlainTag;
use App\Models\Series;
use App\Models\Tag;
use App\Models\Tag\Scopes\ByTaggableType;
use App\Models\UserTaggable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByTaggableTypeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_tags_by_type()
    {
        Tag::factory()->series()->count(3)->create();
        Tag::factory()->more()->count(5)->create();

        $this->assertCount(3, Tag::scoped(new ByTaggableType(Series::MORPH_ALIAS))->get());
        $this->assertCount(5, Tag::scoped(new ByTaggableType(PlainTag::MORPH_ALIAS))->get());
    }

    /** @test */
    public function it_filters_user_taggables_by_type()
    {
        UserTaggable::factory()->series()->count(3)->create();
        UserTaggable::factory()->more()->count(5)->create();

        $this->assertCount(3, UserTaggable::scoped(new ByTaggableType(Series::MORPH_ALIAS))->get());
        $this->assertCount(5, UserTaggable::scoped(new ByTaggableType(PlainTag::MORPH_ALIAS))->get());
    }
}
