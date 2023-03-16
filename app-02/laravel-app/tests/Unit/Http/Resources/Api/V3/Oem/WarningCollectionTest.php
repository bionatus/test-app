<?php

namespace Tests\Unit\Http\Resources\Api\V3\Oem;

use App\Http\Resources\Api\V3\Oem\WarningCollection;
use App\Http\Resources\Api\V3\Oem\WarningResource;
use App\Models\Warning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarningCollectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        Warning::factory()->count(10)->create();
        $page = Warning::query()->paginate();

        $resource = new WarningCollection($page);
        $response = $resource->resolve();

        $data = [
            'data'           => WarningResource::collection($page),
            'has_more_pages' => $page->hasMorePages(),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(WarningCollection::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
