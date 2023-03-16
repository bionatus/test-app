<?php

namespace Tests\Unit\Http\Resources\Api\V2\Tag;

use App\Http\Resources\Api\V2\Tag\BrandImageResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandImageResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_null_on_bad_data()
    {
        $resource = new BrandImageResource(null);

        $response = $resource->resolve();

        $expected = [
            'id'          => null,
            'url'         => null,
            'conversions' => [],
        ];

        $this->assertEquals($expected, $response);
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $id  = 'the-id';
        $url = 'url';

        $resource = new BrandImageResource(['id' => $id, 'url' => $url]);

        $response = $resource->resolve();

        $data = [
            'id'          => $id,
            'url'         => $url,
            'conversions' => [],
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BrandImageResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_returns_url_as_id_if_id_is_missing()
    {
        $url = 'url';

        $resource = new BrandImageResource(['url' => $url]);

        $response = $resource->resolve();

        $data = [
            'id'          => $url,
            'url'         => $url,
            'conversions' => [],
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BrandImageResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
