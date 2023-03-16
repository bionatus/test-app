<?php

namespace Tests\Unit\Http\Resources\Api\V2\Tag;

use App\Http\Resources\Api\V2\Tag\SeriesImageResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeriesImageResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_null_on_bad_data()
    {
        $resource = new SeriesImageResource(null);

        $response = $resource->resolve();

        $data = [
            'id'          => null,
            'url'         => null,
            'conversions' => [],
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SeriesImageResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $url = 'url';

        $resource = new SeriesImageResource($url);

        $response = $resource->resolve();

        $data = [
            'id'          => $url,
            'url'         => $url,
            'conversions' => [],
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SeriesImageResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
