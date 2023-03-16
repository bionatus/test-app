<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\Brand\ImageResource;
use App\Models\Brand;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Brand $resource
 */
class BrandResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Brand $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $brand = $this->resource;

        return [
            'id'    => $brand->getRouteKey(),
            'name'  => $brand->name,
            'image' => new ImageResource($brand->logo),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'    => ['type' => ['string']],
                'name'  => ['type' => ['string']],
                'image' => ImageResource::jsonSchema(),
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
