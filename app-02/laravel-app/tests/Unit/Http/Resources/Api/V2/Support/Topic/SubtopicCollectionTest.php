<?php

namespace Tests\Unit\Http\Resources\Api\V2\Support\Topic;

use App\Http\Resources\Api\V2\Support\Topic\SubtopicCollection;
use App\Http\Resources\Api\V2\Support\Topic\SubtopicResource;
use App\Models\Subtopic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubtopicCollectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $topic = Subtopic::factory()->create()->topic;

        $resource = new SubtopicCollection($topic->subtopics);

        $response = $resource->resolve();

        $data = [
            'data' => SubtopicResource::collection($topic->subtopics),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SubtopicCollection::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
