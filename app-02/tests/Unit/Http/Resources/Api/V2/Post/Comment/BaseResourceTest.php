<?php

namespace Tests\Unit\Http\Resources\Api\V2\Post\Comment;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V2\ImageCollection;
use App\Http\Resources\Api\V2\Post;
use App\Http\Resources\Api\V2\Post\Comment\BaseResource;
use App\Http\Resources\Api\V2\Post\Comment\TaggedUserCollection;
use App\Http\Resources\Api\V2\UserResource;
use App\Models\Comment;
use App\Models\CommentUser;
use App\Models\CommentVote;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Request;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $comment      = Comment::factory()->create();
        $commentVotes = CommentVote::factory()->usingComment($comment)->count(3)->create()->sortByDesc([
            'created_at',
            CommentVote::keyName(),
        ]);
        CommentUser::factory()->usingComment($comment)->count(2)->create();

        $resource = new BaseResource($comment);

        $response = $resource->toArray(Request::instance());
        $data     = [
            'id'                 => $comment->getRouteKey(),
            'message'            => $comment->message,
            'content_updated_at' => $comment->content_updated_at,
            'created_at'         => $comment->created_at,
            'user'               => new UserResource($comment->user),
            'solution'           => $comment->solution,
            'voted'              => false,
            'votes_count'        => 3,
            'latest_voters'      => Post\Comment\UserResource::collection($commentVotes->pluck('user')),
            'tagged_users'       => new TaggedUserCollection($comment->taggedUsers),
            'images'             => new ImageCollection($comment->getMedia(MediaCollectionNames::IMAGES)),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_knows_if_the_logged_user_already_voted()
    {
        $comment = Comment::factory()->create();
        CommentVote::factory()->usingComment($comment)->create();

        $votedComment = Comment::factory()->create();
        $commentVote  = CommentVote::factory()->usingComment($votedComment)->create();

        Auth::shouldReceive('user')->andReturn($commentVote->user);

        $resource      = new BaseResource($comment);
        $votedResource = new BaseResource($votedComment);

        $response      = $resource->toArray(Request::instance());
        $votedResponse = $votedResource->toArray(Request::instance());

        $this->assertFalse($response['voted']);
        $this->assertTrue($votedResponse['voted']);
    }
}
