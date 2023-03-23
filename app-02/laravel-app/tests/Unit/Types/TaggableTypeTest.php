<?php

namespace Tests\Unit\Types;

use App\Models\IsTaggable;
use App\Models\Media;
use App\Models\PlainTag;
use App\Models\Series;
use App\Models\Tag;
use App\Services\TaggableQuery;
use App\Types\TaggableType;
use Exception;
use Illuminate\Support\Collection;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class TaggableTypeTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_does_not_allow_invalid_items()
    {
        $this->expectException(Exception::class);

        new TaggableType(['invalid']);
    }

    /**
     * @test
     *
     * @param array $element
     *
     * @dataProvider invalidElementDataProvider
     * @throws Exception
     */
    public function it_requires_id_and_type(array $element)
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid element. The element needs to have an id and type.');

        new TaggableType($element);
    }

    public function invalidElementDataProvider(): array
    {
        return [
            [[]],
            [
                [
                    'id' => 'an-id',
                ],
            ],
            [
                [
                    'type' => 'a-type',
                ],
            ],
        ];
    }

    /** @test */
    public function type_must_be_a_valid_value()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid type.');

        new TaggableType([
            'id'   => 'an-id',
            'name' => 'a-name',
            'type' => 'an-invalid-type',
        ]);
    }

    /** @test */
    public function it_returns_a_taggable_query()
    {
        $taggableQuery = TaggableType::query(new Collection());

        $this->assertInstanceOf(TaggableQuery::class, $taggableQuery);
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_an_array_representation()
    {
        $data = [
            'id'   => 'an-id',
            'name' => 'a-name',
            'type' => Tag::TYPE_GENERAL,
        ];

        $taggableType = new TaggableType($data);

        $this->assertIsArray($taggableType->toArray());
        $this->assertEquals($data, $taggableType->toArray());
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_its_associated_taggable_model_instance()
    {
        $this->refreshDatabaseForSingleTest();

        $urchin = new TaggableType([
            'id'   => 'invalid',
            'type' => Tag::TYPE_SERIES,
        ]);

        $series = Series::factory()->create();
        $real   = new TaggableType([
            'id'   => $series->getRouteKey(),
            'type' => Tag::TYPE_SERIES,
        ]);

        $this->assertNull($urchin->taggable());
        $this->assertInstanceOf(IsTaggable::class, $real->taggable());
        $this->assertSame($series->getKey(), $real->taggable()->getKey());
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_can_have_a_name_different_from_his_id()
    {
        $name = 'valid';

        $taggableType = new TaggableType([
            'id'   => 1,
            'type' => Tag::TYPE_GENERAL,
            'name' => $name,
        ]);

        $this->assertSame($name, $taggableType->name);
    }

    /** @test
     * @throws Exception
     */
    public function it_allows_store_and_retrieve_of_media()
    {
        $this->refreshDatabaseForSingleTest();

        $tag   = PlainTag::factory()->issue()->create();
        $media = Media::factory()->usingTag($tag)->create();

        $taggableType = new TaggableType([
            'id'    => $tag->id,
            'type'  => $tag->type,
            'name'  => $tag->name,
            'media' => $media,
        ]);

        $taggableMedia = $taggableType->getMedia();

        $this->assertIsArray($taggableType->getMedia());
        $this->assertCount(1, $taggableMedia);
        $this->assertInstanceOf(Media::class, $taggableMedia[0]);
        $this->assertSame($media->uuid, $taggableMedia[0]->uuid);
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_an_empty_array_if_no_media_provided()
    {
        $taggableType = new TaggableType([
            'id'   => 1,
            'type' => Tag::TYPE_GENERAL,
            'name' => 'name',
        ]);

        $this->assertIsArray($taggableType->getMedia());
        $this->assertCount(0, $taggableType->getMedia());
    }

    /** @test */
    public function connector_must_be_a_valid_value()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid connector.');

        new TaggableType([
            'id'        => 'an-id',
            'name'      => 'a-name',
            'type'      => Tag::TYPE_GENERAL,
            'connector' => 'invalid',
        ]);
    }

    /**
     * @test
     *
     * @param string $connector
     *
     * @dataProvider connectorDataProvider
     * @throws Exception
     */
    public function it_knows_its_connector(string $connector)
    {
        $data = [
            'id'        => 'an-id',
            'name'      => 'a-name',
            'type'      => Tag::TYPE_GENERAL,
            'connector' => $connector,
        ];

        $taggableType = new TaggableType($data);

        $this->assertEquals($connector, $taggableType->connector());
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_an_and_connector_as_default()
    {
        $data = [
            'id'   => 'an-id',
            'name' => 'a-name',
            'type' => Tag::TYPE_GENERAL,
        ];

        $taggableType = new TaggableType($data);

        $this->assertEquals(TaggableType::CONNECTOR_AND, $taggableType->connector());
    }

    public function connectorDataProvider(): array
    {
        return [
            [TaggableType::CONNECTOR_OR],
            [TaggableType::CONNECTOR_AND],
        ];
    }
}
