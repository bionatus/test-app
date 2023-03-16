<?php

namespace Tests\Unit\Models\Tag\Scopes;

use App\Models\PlainTag;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByTaggableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_type_and_id()
    {
        $modelTypeTag = Tag::factory()->modelType()->create();
        $seriesTag    = Tag::factory()->series()->create();
        $plainTagTag  = Tag::factory()->more()->create();

        $this->assertCount(1, Tag::scoped(new Tag\Scopes\ByTaggable($modelTypeTag->taggable))->get());
        $this->assertCount(1, Tag::scoped(new Tag\Scopes\ByTaggable($seriesTag->taggable))->get());
        $this->assertCount(1, Tag::scoped(new Tag\Scopes\ByTaggable($plainTagTag->taggable))->get());
    }

    /** @test */
    public function it_checks_existence_of_taggable()
    {
        $nonPersistedTaggable     = new PlainTag();
        $nonPersistedTaggable->id = 1;
        Tag::factory()->usingPlainTag($nonPersistedTaggable)->create();

        $this->assertCount(0, Tag::scoped(new Tag\Scopes\ByTaggable($nonPersistedTaggable))->get());
    }
}
