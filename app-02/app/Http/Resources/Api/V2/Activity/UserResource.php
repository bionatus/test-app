<?php

namespace App\Http\Resources\Api\V2\Activity;

use App\Http\Resources\HasJsonSchema;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property User $resource
 */
class UserResource extends JsonResource implements HasJsonSchema
{
    public function __construct(User $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'id'         => $this->resource->getRouteKey(),
            'first_name' => $this->resource->first_name,
            'last_name'  => $this->resource->last_name,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'         => ['type' => ['integer']],
                'first_name' => ['type' => ['string']],
                'last_name'  => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'first_name',
                'last_name',
            ],
            'additionalProperties' => false,
        ];
    }
}
