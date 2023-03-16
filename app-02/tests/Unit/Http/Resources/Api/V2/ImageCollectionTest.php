<?php

namespace Tests\Unit\Http\Resources\Api\V2;

use App\Http\Resources\Api\V2\ImageCollection;
use App\Http\Resources\Models\ImageResource;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImageCollectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $images = Media::factory()->count(2)->create();

        $resource = new ImageCollection($images);
        $response = $resource->resolve();

        $data = [
            'data' => ImageResource::collection($images),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ImageCollection::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
