<?php

namespace Tests\Unit\Http\Resources\Api\V2\Tag;

use App\Http\Resources\Api\V2\Tag\BrandImageCollection;
use App\Http\Resources\Api\V2\Tag\BrandImageResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandImageCollectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $images   = [];
        $resource = new BrandImageCollection($images);
        $response = $resource->resolve();

        $data = [
            'data' => BrandImageResource::collection($images),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BrandImageCollection::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
