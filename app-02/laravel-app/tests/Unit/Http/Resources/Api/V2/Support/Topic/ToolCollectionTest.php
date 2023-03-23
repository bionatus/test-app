<?php

namespace Tests\Unit\Http\Resources\Api\V2\Support\Topic;

use App\Http\Resources\Api\V2\Support\Topic\ToolCollection;
use App\Http\Resources\Api\V2\Support\Topic\ToolResource;
use App\Models\Tool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToolCollectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $tools = Tool::factory()->count(3)->create();

        $resource = new ToolCollection($tools);

        $response = $resource->resolve();

        $data = [
            'data' => ToolResource::collection($tools),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ToolCollection::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
