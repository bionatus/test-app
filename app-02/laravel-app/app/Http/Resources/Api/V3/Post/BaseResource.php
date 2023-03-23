<?php

namespace App\Http\Resources\Api\V3\Post;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\PostResource;
use App\Models\Post;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Post $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private PostResource $baseResource;

    public function __construct(Post $resource)
    {
        parent::__construct($resource);

        $this->baseResource = new PostResource($resource);
    }

    public function toArray($request)
    {
        $post = $this->resource;
        $post->loadMissingCount('comments');
        $post->loadMissingCount('votes');

        $baseResource = $this->baseResource->toArray($request);

        return array_merge_recursive($baseResource, [
            'total_comments'   => $post->comments_count,
            'voted'            => !!$post->authUserVote,
            'votes_count'      => $post->votes_count,
            'solution_comment' => $post->isSolved() ? new CommentResource($post->solutionComment) : null,
        ]);
    }

    public static function jsonSchema(): array
    {
        return array_merge_recursive(PostResource::jsonSchema(), [
            'properties' => [
                'total_comments'   => ['type' => ['integer']],
                'voted'            => ['type' => ['boolean']],
                'votes_count'      => ['type' => ['integer']],
                'solution_comment' => CommentResource::jsonSchema(),
            ],
            'required'   => [
                'total_comments',
                'video_url',
                'voted',
                'votes_count',
                'solution_comment',
            ],
        ]);
    }
}
