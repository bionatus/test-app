<?php

namespace App\Http\Resources\LiveApi\V1\Supplier;

use App\Http\Resources\HasJsonSchema;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BrandCollection extends ResourceCollection implements HasJsonSchema
{
    public function toArray($request)
    {
        return [
            'data' => BrandResource::collection($this->collection),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'data' => [
                    'type'  => ['array'],
                    'items' => BrandResource::jsonSchema(),
                ],
            ],
            'required'             => [
                'data',
            ],
            'additionalProperties' => false,
        ];
    }
}
