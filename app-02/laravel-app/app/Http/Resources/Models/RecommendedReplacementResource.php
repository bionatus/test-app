<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\RecommendedReplacement;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property RecommendedReplacement $resource
 */
class RecommendedReplacementResource extends JsonResource implements HasJsonSchema
{
    public function __construct(RecommendedReplacement $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $resource = $this->resource;

        return [
            'id'            => $resource->getRouteKey(),
            'supplier'      => $resource->supplier ? new SupplierResource($resource->supplier) : null,
            'original_part' => new PartResource($resource->part),
            'brand'         => $resource->brand,
            'part_number'   => $resource->part_number,
            'note'          => $resource->note,
        ];
    }

    public static function jsonSchema(): array
    {
        $supplierResource           = SupplierResource::jsonSchema();
        $supplierResource['type'][] = 'null';

        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'            => ['type' => ['integer']],
                'supplier'      => $supplierResource,
                'original_part' => PartResource::jsonSchema(),
                'brand'         => ['type' => ['string']],
                'part_number'   => ['type' => ['string']],
                'note'          => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'id',
                'supplier',
                'original_part',
                'brand',
                'part_number',
                'note',
            ],
            'additionalProperties' => false,
        ];
    }
}
