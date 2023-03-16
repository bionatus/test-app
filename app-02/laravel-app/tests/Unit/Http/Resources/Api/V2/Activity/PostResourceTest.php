<?php

namespace Tests\Unit\Http\Resources\Api\V2\Activity;

use App\Http\Resources\Api\V2\Activity\PostResource;
use App\Http\Resources\Api\V2\Activity\UserResource;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Request;
use Tests\TestCase;

class PostResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $post = Post::factory()->create();

        $resource = new PostResource($post);
        $response = $resource->toArray(Request::instance());
        $data     = [
            'id'   => $post->getRouteKey(),
            'user' => new UserResource($post->user),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(PostResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
