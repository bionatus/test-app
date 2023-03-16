<?php

namespace App\Http\Resources\LiveApi\V1\LimitedSupplier;

use App\Models\Supplier;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property Supplier $resource */
class BaseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'              => $this->resource->getRouteKey(),
            'take_rate'       => round($this->resource->take_rate / 100, 2),
            'take_rate_until' => $this->resource->take_rate_until,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'              => ['type' => ['string']],
                'take_rate'       => ['type' => ['number']],
                'take_rate_until' => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'take_rate',
                'take_rate_until',
            ],
            'additionalProperties' => false,
        ];
    }
}
