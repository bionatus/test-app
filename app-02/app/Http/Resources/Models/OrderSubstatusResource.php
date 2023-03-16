<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\OrderSubstatus;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property OrderSubstatus $resource
 */
class OrderSubstatusResource extends JsonResource implements HasJsonSchema
{
    public function __construct(OrderSubstatus $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $orderSubstatus = $this->resource;
        $substatus      = $orderSubstatus->substatus;

        return [
            'status'     => $substatus->status->getRouteKey(),
            'substatus'  => $substatus->getRouteKey(),
            'detail'     => $orderSubstatus->detail,
            'created_at' => $orderSubstatus->created_at,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'status'     => ['type' => ['string']],
                'substatus'  => ['type' => ['string']],
                'detail'     => ['type' => ['string', 'null']],
                'created_at' => ['type' => ['string']],
            ],
            'required'             => [
                'status',
                'substatus',
                'detail',
                'created_at',
            ],
            'additionalProperties' => false,
        ];
    }
}
