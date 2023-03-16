<?php

namespace Tests\Unit\Http\Resources\Api\V2\Activity;

use App\Http\Resources\Api\V2\Activity\CommentResource;
use App\Http\Resources\Api\V2\Activity\PostResource;
use App\Http\Resources\Api\V2\Activity\UserResource;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Request;
use Tests\TestCase;

class CommentResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $comment = Comment::factory()->create();

        $resource = new CommentResource($comment);
        $response = $resource->toArray(Request::instance());
        $data     = [
            'id'   => $comment->getRouteKey(),
            'user' => new UserResource($comment->user),
            'post' => new PostResource($comment->post),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(CommentResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
