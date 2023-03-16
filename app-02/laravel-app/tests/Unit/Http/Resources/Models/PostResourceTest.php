<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V2\ImageCollection;
use App\Http\Resources\Api\V2\Post\TagCollection;
use App\Http\Resources\Api\V2\UserResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\PostResource;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class PostResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(PostResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $post   = Post::factory()->create();
        $images = $post->getMedia(MediaCollectionNames::IMAGES);

        $response = (new PostResource($post))->resolve();

        $data = [
            'id'         => $post->getRouteKey(),
            'message'    => $post->message,
            'video_url'  => $post->video_url,
            'type'       => $post->type,
            'created_at' => $post->created_at,
            'pinned'     => false,
            'user'       => new UserResource($post->user),
            'tags'       => new TagCollection($post->tags),
            'images'     => new ImageCollection($images),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(PostResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $post = Post::factory()->pinned()->create(['video_url' => 'http:://testurl.com']);

        $this->login($post->user);

        $response = (new PostResource($post))->resolve();

        $images = $post->getMedia(MediaCollectionNames::IMAGES);

        $data = [
            'id'         => $post->getRouteKey(),
            'message'    => $post->message,
            'video_url'  => $post->video_url,
            'type'       => $post->type,
            'created_at' => $post->created_at,
            'pinned'     => true,
            'user'       => new UserResource($post->user),
            'tags'       => new TagCollection($post->tags),
            'images'     => new ImageCollection($images),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(PostResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
