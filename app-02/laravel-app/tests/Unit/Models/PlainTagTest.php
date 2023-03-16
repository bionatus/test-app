<?php

namespace Tests\Unit\Models;

use App\Models\IsTaggable;
use App\Models\Media;
use App\Models\PlainTag;
use App\Types\TaggableType;
use Exception;

class PlainTagTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(PlainTag::tableName(), [
            'id',
            'slug',
            'name',
            'type',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_uses_slug()
    {
        $plainTag = PlainTag::factory()->create(['slug' => 'something']);

        $this->assertEquals($plainTag->slug, $plainTag->getRouteKey());
    }

    /** @test */
    public function it_fills_slug_on_creation()
    {
        $plainTag = PlainTag::factory()->make(['slug' => null]);
        $plainTag->save();

        $this->assertNotNull($plainTag->slug);
    }

    /** @test */
    public function it_is_a_taggable()
    {
        $taggable = new PlainTag();

        $this->assertInstanceOf(IsTaggable::class, $taggable);
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_raw_tag_representation_of_a_more_type()
    {
        $more = PlainTag::factory()->more()->create();

        $rawTag = $more->toTagType();
        $this->assertInstanceOf(TaggableType::class, $rawTag);

        $this->assertEquals($more->getRouteKey(), $rawTag->id);
        $this->assertSame($more->type, $rawTag->type);
        $this->assertSame($more->name, $rawTag->name);
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_raw_tag_representation_of_a_general_type()
    {
        $general = PlainTag::factory()->general()->create();

        $rawTag = $general->toTagType();
        $this->assertInstanceOf(TaggableType::class, $rawTag);

        $this->assertEquals($general->getRouteKey(), $rawTag->id);
        $this->assertSame($general->type, $rawTag->type);
        $this->assertSame($general->name, $rawTag->name);
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_raw_tag_representation_of_an_issue_type()
    {
        $issue = PlainTag::factory()->issue()->create();

        $rawTag = $issue->toTagType();
        $this->assertInstanceOf(TaggableType::class, $rawTag);

        $this->assertEquals($issue->getRouteKey(), $rawTag->id);
        $this->assertSame($issue->type, $rawTag->type);
        $this->assertSame($issue->name, $rawTag->name);
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_the_media_if_required()
    {
        $issue = PlainTag::factory()->issue()->create();
        $media = Media::factory()->usingTag($issue)->create();

        $rawTag = $issue->toTagType(true);
        $this->assertInstanceOf(TaggableType::class, $rawTag);

        $this->assertEquals($issue->getRouteKey(), $rawTag->id);
        $this->assertSame($issue->type, $rawTag->type);
        $this->assertSame($issue->name, $rawTag->name);
        $this->assertSame($media->uuid, $rawTag->getMedia()[0]->uuid);
    }

    /** @test */
    public function it_returns_a_taggable_route_key()
    {
        $issue = PlainTag::factory()->issue()->create();

        $this->assertEquals($issue->type . '-' . $issue->getRouteKey(), $issue->taggableRouteKey());
    }
}
