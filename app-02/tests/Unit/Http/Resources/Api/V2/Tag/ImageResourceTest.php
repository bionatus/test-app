<?php

namespace Tests\Unit\Http\Resources\Api\V2\Tag;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V2\Tag\ImageResource;
use App\Models\Media;
use App\Models\PlainTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImageResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $media = Media::factory()->create([
            'model_type'      => PlainTag::MORPH_ALIAS,
            'collection_name' => MediaCollectionNames::IMAGES,
        ]);

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
