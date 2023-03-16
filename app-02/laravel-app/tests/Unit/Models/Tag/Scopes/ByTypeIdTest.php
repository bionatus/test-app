<?php

namespace Tests\Unit\Models\Tag\Scopes;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByTypeIdTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_type_and_id()
    {
        $seriesTag   = Tag::factory()->series()->create();
        $plainTagTag = Tag::factory()->more()->create();

        $this->assertCount(1,
            Tag::scoped(new Tag\Scopes\ByTypeId($seriesTag->taggable_type, $seriesTag->taggable_id))->get());
        $this->assertCount(1,
            Tag::scoped(new Tag\Scopes\ByTypeId($plainTagTag->taggable_type, $plainTagTag->taggable_id))->get());
    }

    /** @test */
    public function it_filters_nothing_on_an_empty_type_or_id()
    {
        Tag::factory()->count(3)->create();

        $this->assertCount(3, Tag::scoped(new Tag\Scopes\ByTypeId(null, null))->get());
    }
}
