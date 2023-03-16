<?php

namespace App\Http\Resources\Api\V3\Activity;

use App\Http\Resources\HasJsonSchema;
use App\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Order $resource
 */
class OrderResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Order $resource)
    {
        parent::__construct($resource);
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'            => ['type' => ['string']],
                'name'          => ['type' => ['string', 'null']],
                'status'        => ['type' => ['string']],
                'working_on_it' => ['type' => ['string', 'null']],
                'created_at'    => ['type' => ['string']],
                'updated_at'    => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'name',
                'status',
                'working_on_it',
                'created_at',
                'updated_at',
            ],
            'additionalProperties' => false,
        ];
    }

    public function toArray($request)
    {
        return [
            'id'            => $this->resource->getRouteKey(),
            'name'          => $this->resource->name,
            'working_on_it' => $this->resource->working_on_it,
            'status'        => $this->resource->getStatusName(),
            'created_at'    => $this->resource->created_at,
            'updated_at'    => $this->resource->updated_at,
        ];
    }
}
