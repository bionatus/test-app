<?php

namespace Tests\Unit\Http\Resources\Api\V2\Support\Topic;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V2\ImageCollection;
use App\Http\Resources\Api\V2\Support\Topic\ToolResource;
use App\Models\Tool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToolResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $tool = Tool::factory()->create();

        $resource = new ToolResource($tool);

        $response = $resource->resolve();

        $data = [
            'id'     => $tool->getRouteKey(),
            'name'   => $tool->name,
            'images' => new ImageCollection($tool->getMedia(MediaCollectionNames::IMAGES)),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ToolResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
