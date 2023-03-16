<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\SupplierHour;
use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property SupplierHour $resource
 */
class SupplierHourResource extends JsonResource implements HasJsonSchema
{
    public function __construct(SupplierHour $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'day'  => $this->resource->day,
            'from' => Carbon::create($this->resource->from)->format('H:i'),
            'to'   => Carbon::create($this->resource->to)->format('H:i'),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'day'  => ['type' => ['string']],
                'from' => ['type' => ['string']],
                'to'   => ['type' => ['string']],
            ],
            'required'             => [
                'day',
                'from',
                'to',
            ],
            'additionalProperties' => false,
        ];
    }
}
