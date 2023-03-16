<?php

namespace Tests\Unit\Http\Resources\Api\V3\User\Post;

use App\Http\Resources\Api\V3\User\Post\CommentResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\UserResource;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class CommentResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(CommentResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $comment = Comment::factory()->create();

        $response = (new CommentResource($comment))->resolve();

        $data = [
            'id'       => $comment->getRouteKey(),
            'solution' => $comment->solution,
            'user'     => new UserResource($comment->user),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(CommentResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
