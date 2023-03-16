<?php

namespace Tests\Unit\Rules;

use App\Models\Series;
use App\Models\Tag;
use App\Rules\ExistingIncomingRawTag;
use App\Types\TaggableType;
use Exception;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class ExistingIncomingRawTagTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_should_fail_if_the_item_is_not_an_array()
    {
        $existingIncomingRawTag = new ExistingIncomingRawTag();

        $this->assertFalse($existingIncomingRawTag->passes('valid', 'invalid'));
        $this->assertSame('Tag must be an array.', $existingIncomingRawTag->message());
    }

    /** @test */
    public function it_should_fail_if_the_item_does_not_have_a_type_key()
    {
        $existingIncomingRawTag = new ExistingIncomingRawTag();

        $this->assertFalse($existingIncomingRawTag->passes('valid', []));
        $this->assertSame('Tag must have a "type" key.', $existingIncomingRawTag->message());
    }

    /** @test */
    public function it_should_fail_if_the_item_does_not_have_an_id_key()
    {
        $existingIncomingRawTag = new ExistingIncomingRawTag();

        $this->assertFalse($existingIncomingRawTag->passes('valid', ['type' => 1]));
        $this->assertSame('Tag must have an "id" key.', $existingIncomingRawTag->message());
    }

    /** @test */
    public function it_should_fail_if_the_item_type_is_not_string()
    {
        $existingIncomingRawTag = new ExistingIncomingRawTag();

        $this->assertFalse($existingIncomingRawTag->passes('valid', ['type' => 1, 'id' => 'invalid']));
        $this->assertSame('Tag Type must be a string.', $existingIncomingRawTag->message());
    }

    /** @test */
    public function it_should_fail_if_the_item_id_is_not_string_or_integer()
    {
        $existingIncomingRawTag = new ExistingIncomingRawTag();

        $this->assertFalse($existingIncomingRawTag->passes('valid', ['type' => 'valid', 'id' => []]));
        $this->assertSame('Tag ID must be a string or integer.', $existingIncomingRawTag->message());
    }

    /** @test */
    public function it_should_fail_if_the_item_type_is_invalid()
    {
        $existingIncomingRawTag = new ExistingIncomingRawTag();

        $this->assertFalse($existingIncomingRawTag->passes('valid', ['type' => 'invalid', 'id' => 'valid']));
        $this->assertSame('Invalid tag.', $existingIncomingRawTag->message());
    }

    /** @test */
    public function it_should_fail_if_the_item_connector_is_invalid()
    {
        $this->refreshDatabaseForSingleTest();
        $existingIncomingRawTag = new ExistingIncomingRawTag();

        $series = Series::factory()->create();

        $this->assertFalse($existingIncomingRawTag->passes('valid', [
            'connector' => 'invalid',
            'id'        => $series->getRouteKey(),
            'type'      => $series->morphType(),
        ]));

        $this->assertSame('Invalid tag.', $existingIncomingRawTag->message());
    }

    /** @test */
    public function it_should_fail_if_the_item_is_not_in_database()
    {
        $existingIncomingRawTag = new ExistingIncomingRawTag();

        $this->assertFalse($existingIncomingRawTag->passes('valid', ['type' => Tag::TYPE_SERIES, 'id' => 'invalid']));
        $this->assertSame('Invalid tag.', $existingIncomingRawTag->message());
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_taggables()
    {
        $this->refreshDatabaseForSingleTest();

        $existingIncomingRawTag = new ExistingIncomingRawTag();

        $series = Series::factory()->create();

        $existingIncomingRawTag->passes('valid', $series->toTagType()->toArray());

        $this->assertCount(1, $existingIncomingRawTag->taggables());

        $this->assertInstanceOf(Series::class, $existingIncomingRawTag->taggables()->first());

        $this->assertSame($series->getKey(), $existingIncomingRawTag->taggables()->first()->getKey());
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_collection_of_taggable_types()
    {
        $this->refreshDatabaseForSingleTest();

        $existingIncomingRawTag = new ExistingIncomingRawTag();

        $series = Series::factory()->create();

        $existingIncomingRawTag->passes('valid', $series->toTagType()->toArray());

        $this->assertCount(1, $existingIncomingRawTag->taggableTypes());

        /** @var TaggableType $taggableType */
        $taggableType = $existingIncomingRawTag->taggableTypes()->first();
        $this->assertInstanceOf(TaggableType::class, $taggableType);

        $this->assertEquals($series->toTagType()->toArray(), $taggableType->toArray());
    }
}
