<?php

namespace App\Http\Resources\Api\V2\Activity;

use App\Http\Resources\HasJsonSchema;
use App\Models\Activity;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Activity $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Activity $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'id'         => $this->resource->getRouteKey(),
            'resource'   => $this->resource->resource,
            'event'      => $this->resource->event,
            'log_name'   => $this->resource->log_name,
            'payload'    => $this->resource->properties,
            'created_at' => $this->resource->created_at,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'         => ['type' => ['integer']],
                'resource'   => ['type' => ['string']],
                'event'      => ['type' => ['string']],
                'log_name'   => ['type' => ['string']],
                'payload'    => [
                    'anyOf' => [
                        ['type' => ['string']],
                        CommentResource::jsonSchema(),
                        PostResource::jsonSchema(),
                    ],
                ],
                'created_at' => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'resource',
                'event',
                'log_name',
                'payload',
                'created_at',
            ],
            'additionalProperties' => false,
        ];
    }
}
