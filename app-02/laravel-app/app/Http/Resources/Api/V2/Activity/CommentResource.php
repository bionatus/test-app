<?php

namespace App\Http\Resources\Api\V2\Activity;

use App\Http\Resources\HasJsonSchema;
use App\Models\Comment;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Comment $resource
 */
class CommentResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Comment $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'id'   => $this->resource->getRouteKey(),
            'user' => new UserResource($this->resource->user),
            'post' => new PostResource($this->resource->post),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'   => ['type' => ['string']],
                'user' => UserResource::jsonSchema(),
                'post' => PostResource::jsonSchema(),
            ],
            'required'             => [
                'id',
                'user',
                'post',
            ],
            'additionalProperties' => false,
        ];
    }
}
