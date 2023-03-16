<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\ItemOrder;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ItemOrder $resource
 */
class ItemOrderResource extends JsonResource implements HasJsonSchema
{
    public function __construct(ItemOrder $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        return [
            'id'                       => $this->resource->getRouteKey(),
            'quantity'                 => $this->resource->quantity,
            'quantity_requested'       => $this->resource->quantity_requested,
            'price'                    => $this->resource->price,
            'status'                   => $this->resource->status,
            'supply_detail'            => $this->resource->supply_detail,
            'custom_detail'            => $this->resource->custom_detail,
            'generic_part_description' => $this->resource->generic_part_description,
            'initial_request'          => !!$this->resource->initial_request,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'                       => ['type' => ['string']],
                'quantity'                 => ['type' => ['integer']],
                'quantity_requested'       => ['type' => ['integer']],
                'price'                    => ['type' => ['number', 'null']],
                'status'                   => ['type' => ['string']],
                'supply_detail'            => ['type' => ['string', 'null']],
                'custom_detail'            => ['type' => ['string', 'null']],
                'generic_part_description' => ['type' => ['string', 'null']],
                'initial_request'          => ['type' => 'boolean'],
            ],
            'required'             => [
                'id',
                'quantity',
                'quantity_requested',
                'price',
                'status',
                'supply_detail',
                'custom_detail',
                'generic_part_description',
                'initial_request',
            ],
            'additionalProperties' => false,
        ];
    }
}
