<?php

namespace Tests\Unit\Http\Resources\Api\V2\Support\Topic;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V2\ImageCollection;
use App\Http\Resources\Api\V2\Support\Topic\BaseResource;
use App\Http\Resources\Api\V2\Support\Topic\SubtopicCollection;
use App\Http\Resources\Api\V2\Support\Topic\ToolCollection;
use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $topic = Topic::factory()->create();

        $resource = new BaseResource($topic);
        $response = $resource->resolve();

        $data = [
            'id'          => $topic->subject->getRouteKey(),
            'name'        => $topic->subject->name,
            'description' => $topic->description,
            'subtopics'   => new SubTopicCollection($topic->subtopics),
            'images'      => new ImageCollection($topic->subject->getMedia(MediaCollectionNames::IMAGES)),
            'tools'       => new ToolCollection($topic->subject->tools),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
