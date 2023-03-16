<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\LiveApi\V1\Oem\WarningCollection;
use App\Http\Resources\Models\WarningResource;
use App\Models\Warning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarningCollectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $warnings = Warning::factory()->count(10)->create();

        $resource = new WarningCollection($warnings);
        $response = $resource->resolve();

        $data = [
            'data' => WarningResource::collection($warnings),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(WarningCollection::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
