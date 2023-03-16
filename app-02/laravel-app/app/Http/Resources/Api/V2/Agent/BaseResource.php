<?php

namespace App\Http\Resources\Api\V2\Agent;

use App\Http\Resources\Api\V2\UserResource;
use App\Http\Resources\HasJsonSchema;
use App\Models\Agent;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Agent $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Agent $resource)
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
