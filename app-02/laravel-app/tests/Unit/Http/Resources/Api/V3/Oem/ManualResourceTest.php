<?php

namespace Tests\Unit\Http\Resources\Api\V3\Oem;

use App\Http\Resources\Api\V3\Oem\ManualResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {

        $resource = new ManualResource($url = 'http://manual.pdf');

        $response = $resource->resolve();

        $expected = [
            'id'          => $response['id'],
            'url'         => $url,
            'conversions' => [],
        ];

        $this->assertArrayHasKeysAndValues($expected, $response);
        $schema = $this->jsonSchema(ManualResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
