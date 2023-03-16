<?php

namespace App\Http\Resources\Models;

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

    public function toArray($request): array
    {
        return [
            'id'       => $this->resource->getRouteKey(),
            'message'  => $this->resource->message,
            'solution' => !!$this->resource->solution,
            'user'     => new UserResource($this->resource->user),
        ];
    }

    public static function jsonSchema(bool $nullable = false): array
    {
        return [
            'type'                 => $nullable ? ['object', 'null'] : ['object'],
            'properties'           => [
                'id'       => ['type' => ['string']],
                'message'  => ['type' => ['string']],
                'solution' => ['type' => ['boolean']],
                'user'     => UserResource::jsonSchema(),
            ],
            'required'             => [
                'id',
                'message',
                'solution',
                'user',
            ],
            'additionalProperties' => false,
        ];
    }
}
