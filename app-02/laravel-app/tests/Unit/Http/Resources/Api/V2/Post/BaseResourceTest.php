<?php

namespace Tests\Unit\Http\Resources\Api\V2\Post;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V2\ImageCollection;
use App\Http\Resources\Api\V2\Post\BaseResource;
use App\Http\Resources\Api\V2\Post\TagCollection;
use App\Http\Resources\Api\V2\UserResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostVote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $post = Post::factory()->pinned()->create();
        Comment::factory()->usingPost($post)->solution()->create();
        PostVote::factory()->usingPost($post)->usingUser($post->user)->create();
        $this->login($post->user);

        $resource = new BaseResource($post);

        $response = $resource->resolve();

        $images = $post->getMedia(MediaCollectionNames::IMAGES);

        $data = [
            'id'             => $post->getRouteKey(),
            'message'        => $post->message,
            'video_url'      => $post->video_url,
            'type'           => $post->type,
            'created_at'     => $post->created_at,
            'total_comments' => 1,
            'voted'          => true,
            'votes_count'    => 1,
            'solved'         => true,
            'pinned'         => true,
            'user'           => new UserResource($post->user),
            'tags'           => new TagCollection($post->tags),
            'images'         => new ImageCollection($images),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $post = Post::factory()->create();

        $resource = new BaseResource($post);

        $response = $resource->resolve();

        $images = $post->getMedia(MediaCollectionNames::IMAGES);

        $data = [
            'id'             => $post->getRouteKey(),
            'message'        => $post->message,
            'video_url'      => null,
            'type'           => $post->type,
            'created_at'     => $post->created_at,
            'total_comments' => null,
            'voted'          => false,
            'votes_count'    => null,
            'solved'         => false,
            'pinned'         => false,
            'user'           => new UserResource($post->user),
            'tags'           => new TagCollection($post->tags),
            'images'         => new ImageCollection($images),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
