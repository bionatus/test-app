<?php

namespace App\Http\Resources\Api\V2\Activity;

use App\Http\Resources\HasJsonSchema;
use App\Models\Post;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Post $resource
 */
class PostResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Post $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'id'   => $this->resource->getRouteKey(),
            'user' => new UserResource($this->resource->user),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'   => ['type' => ['string']],
                'user' => UserResource::jsonSchema(),
            ],
            'required'             => [
                'id',
                'user',
            ],
            'additionalProperties' => false,
        ];
    }
}
