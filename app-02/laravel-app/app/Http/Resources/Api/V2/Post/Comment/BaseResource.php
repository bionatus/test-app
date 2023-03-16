<?php

namespace App\Http\Resources\Api\V2\Post\Comment;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V2\ImageCollection;
use App\Http\Resources\Api\V2\Post;
use App\Http\Resources\Api\V2\UserResource;
use App\Http\Resources\HasJsonSchema;
use App\Models\Comment;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Comment $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Comment $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $this->resource->loadCount(['votes']);

        return [
            'id'                 => $this->resource->getRouteKey(),
            'message'            => $this->resource->message,
            'content_updated_at' => $this->resource->content_updated_at,
            'created_at'         => $this->resource->created_at,
            'user'               => new UserResource($this->resource->user),
            'solution'           => $this->resource->solution,
            'voted'              => !!$this->resource->authUserVote,
            'votes_count'        => $this->resource->votes_count,
            'latest_voters'      => Post\Comment\UserResource::collection($this->resource->latestFiveVotes->pluck('user')),
            'tagged_users'       => new TaggedUserCollection($this->resource->taggedUsers),
            'images'             => new ImageCollection($this->resource->getMedia(MediaCollectionNames::IMAGES)),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'                 => ['type' => ['string']],
                'message'            => ['type' => ['string']],
                'content_updated_at' => ['type' => ['string', 'null']],
                'created_at'         => ['type' => ['string']],
                'user'               => UserResource::jsonSchema(),
                'solution'           => ['type' => ['boolean', 'null']],
                'voted'              => ['type' => ['boolean']],
                'votes_count'        => ['type' => ['integer', 'null']],
                'latest_voters'      => [
                    'type'  => 'array',
                    'items' => Post\Comment\UserResource::jsonSchema(),
                ],
                'tagged_users'       => TaggedUserCollection::jsonSchema(),
                'images'             => ImageCollection::jsonSchema(),
            ],
            'required'             => [
                'id',
                'message',
                'content_updated_at',
                'created_at',
                'user',
                'solution',
                'voted',
                'votes_count',
                'latest_voters',
                'tagged_users',
                'images',
            ],
            'additionalProperties' => false,
        ];
    }
}
