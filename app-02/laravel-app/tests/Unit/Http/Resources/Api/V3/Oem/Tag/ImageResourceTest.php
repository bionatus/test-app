<?php

namespace Tests\Unit\Http\Resources\Api\V3\Oem\Tag;

use App\Constants\MediaConversionNames;
use App\Http\Resources\Api\V3\Oem\Tag\ImageResource;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImageResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $media = Media::factory()->post()->create(['generated_conversions' => [MediaConversionNames::THUMB => true]]);

        $resource = new ImageResource($media);

        $response = $resource->resolve();

        $expected = [
            'id'          => $media->uuid,
            'url'         => $media->getUrl(),
            'conversions' => [
                'thumb' => $media->getUrl(MediaConversionNames::THUMB),
            ],
        ];

        $this->assertArrayHasKeysAndValues($expected, $response);
        $schema = $this->jsonSchema(ImageResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_does_not_return_thumb_url_if_thumb_conversion_generation_failed()
    {
        $media = Media::factory()->post()->create();

        $resource = new ImageResource($media);

        $response = $resource->resolve();

        $data = [
            'id'          => $media->uuid,
            'url'         => $media->getUrl(),
            'conversions' => [],
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ImageResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
