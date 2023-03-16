<?php

namespace Tests\Unit\Http\Resources\Api\V2\Post;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V2\ImageCollection;
use App\Http\Resources\Api\V2\Post\CommentCollection;
use App\Http\Resources\Api\V2\Post\DetailedResource;
use App\Http\Resources\Api\V2\Post\TagCollection;
use App\Http\Resources\Api\V2\UserResource;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DetailedResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $post = Post::factory()->create();

        $resource = new DetailedResource($post);

        $response = $resource->resolve();

        $images = $post->getMedia(MediaCollectionNames::IMAGES);

        $data = [
            'id'             => $post->getRouteKey(),
            'message'        => $post->message,
            'video_url'      => $post->video_url,
            'type'           => $post->type,
            'created_at'     => $post->created_at,
            'total_comments' => $post->comments_count,
            'voted'          => !!$post->authUserVote,
            'votes_count'    => $post->votes_count,
            'solved'         => $post->isSolved(),
            'pinned'         => $post->pinned,
            'user'           => new UserResource($post->user),
            'tags'           => new TagCollection($post->tags),
            'images'         => new ImageCollection($images),
            'comments'       => new CommentCollection($post->comments()->paginate()),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(DetailedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
