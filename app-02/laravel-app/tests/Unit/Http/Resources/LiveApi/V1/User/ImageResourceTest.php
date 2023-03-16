<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\User;

use App\Http\Resources\LiveApi\V1\User\ImageResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImageResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $image = 'an image url';

        $resource = new ImageResource($image);

        $response = $resource->resolve();

        $data = [
            'id'          => $response['id'],
            'url'         => $image,
            'conversions' => [],
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ImageResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
