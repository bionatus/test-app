<?php

namespace App\Http\Resources\LiveApi\V1\Order;

use App\Http\Resources\HasJsonSchema;
use App\Models\Supplier;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property Supplier $resource */
class SupplierResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Supplier $resource)
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
            'type'                 => ['object'],
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
