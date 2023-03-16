<?php

namespace App\Http\Resources\LiveApi\V1\Order;

use App\Http\Resources\HasJsonSchema;
use App\Models\Staff;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property Staff $resource */
class StaffResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Staff $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'id'   => $this->resource->getRouteKey(),
            'name' => $this->resource->name,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object', 'null'],
            'properties'           => [
                'id'   => ['type' => ['string']],
                'name' => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'name',
            ],
            'additionalProperties' => false,
        ];
    }
}
