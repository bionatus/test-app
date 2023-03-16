<?php

namespace Tests\Unit\Http\Resources\Api\V3\Oem;

use App\Http\Resources\Api\V3\Oem\ConversionJobCollection;
use App\Http\Resources\Api\V3\Oem\ConversionJobResource;
use App\Models\ConversionJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversionJobCollectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        ConversionJob::factory()->count(10)->create();
        $page = ConversionJob::query()->paginate();

        $resource = new ConversionJobCollection($page);
        $response = $resource->resolve();

        $data = [
            'data'           => ConversionJobResource::collection($page),
            'has_more_pages' => $page->hasMorePages(),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ConversionJobCollection::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
