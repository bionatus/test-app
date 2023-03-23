<?php

namespace Tests\Unit\Http\Resources\Api\V2\Tag;

use App\Http\Resources\Api\V2\Tag\ImageCollection;
use App\Http\Resources\Api\V2\Tag\ImagedResource;
use App\Http\Resources\Api\V2\Tag\SeriesImageCollection;
use App\Models\Series;
use App\Models\Tag;
use App\Types\TaggableType;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImagedResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @throws Exception
     */
    public function it_has_correct_fields()
    {
        $rawTag = new TaggableType([
            'id'   => 'the-id',
            'type' => Tag::TYPE_GENERAL,
            'name' => 'name',
        ]);

        $resource = new ImagedResource($rawTag);

        $response = $resource->resolve();

        $data = [
            'id'     => $rawTag->id,
            'type'   => $rawTag->type,
            'name'   => $rawTag->name,
            'images' => new ImageCollection($rawTag->getMedia()),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ImagedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_custom_image_array_for_series()
    {
        $series = Series::factory()->create();
        $rawTag = $series->toTagType();

        $resource = new ImagedResource($rawTag);

        $response = $resource->resolve();

        $data = [
            'id'     => $rawTag->id,
            'type'   => $rawTag->type,
            'name'   => $rawTag->name,
            'images' => new SeriesImageCollection([$series->image]),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ImagedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
