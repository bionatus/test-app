<?php

namespace Tests\Unit\Http\Resources\Api\V2\Support\Topic;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V2\ImageCollection;
use App\Http\Resources\Api\V2\Support\Topic\SubtopicResource;
use App\Http\Resources\Api\V2\Support\Topic\ToolCollection;
use App\Models\Subtopic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubtopicResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $subtopic = Subtopic::factory()->create();

        $resource = new SubtopicResource($subtopic);

        $response = $resource->resolve();

        $data = [
            'id'     => $subtopic->subject->getRouteKey(),
            'name'   => $subtopic->subject->name,
            'images' => new ImageCollection($subtopic->subject->getMedia(MediaCollectionNames::IMAGES)),
            'tools'  => new ToolCollection($subtopic->subject->tools),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SubtopicResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
