<?php

namespace Tests\Unit\Http\Resources\Api\V2\Tag;

use App\Http\Resources\Api\V2\Tag\SeriesImageCollection;
use App\Http\Resources\Api\V2\Tag\SeriesImageResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeriesImageCollectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $images   = [];
        $resource = new SeriesImageCollection($images);
        $response = $resource->resolve();

        $data = [
            'data' => SeriesImageResource::collection($images),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SeriesImageCollection::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
