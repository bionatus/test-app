<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\LiveApi\V1\Oem\ConversionJobCollection;
use App\Http\Resources\Models\ConversionJobResource;
use App\Models\ConversionJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversionJobCollectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $conversionJobs = ConversionJob::factory()->count(10)->create();

        $resource = new ConversionJobCollection($conversionJobs);
        $response = $resource->resolve();

        $data = [
            'data' => ConversionJobResource::collection($conversionJobs),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ConversionJobCollection::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
