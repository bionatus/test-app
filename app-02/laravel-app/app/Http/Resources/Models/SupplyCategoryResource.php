<?php

namespace App\Http\Resources\Models;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Models\SupplyCategory;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property SupplyCategory $resource
 */
class SupplyCategoryResource extends JsonResource implements HasJsonSchema
{
    public function __construct(SupplyCategory $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $supplyCategory = $this->resource;
        $image          = $supplyCategory->getFirstMedia(MediaCollectionNames::IMAGES);

        return [
            'id'    => $supplyCategory->getRouteKey(),
            'name'  => $supplyCategory->name,
            'image' => $image ? new ImageResource($image) : null,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'    => ['type' => ['string']],
                'name'  => ['type' => ['string']],
                'image' => ImageResource::jsonSchema(true),
            ],
            'required'             => [
                'id',
                'name',
                'image',
            ],
            'additionalProperties' => false,
        ];
    }
}
