<?php

namespace App\Http\Resources\Api\V2\Tag;

use App\Http\Resources\HasJsonSchema;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BrandImageCollection extends ResourceCollection implements HasJsonSchema
{
    public function toArray($request)
    {
        return [
            'data' => BrandImageResource::collection($this->collection),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'data' => [
                    'type'  => ['array'],
                    'items' => BrandImageResource::jsonSchema(),
                ],
            ],
            'required'             => [
                'data',
            ],
            'additionalProperties' => false,
        ];
    }
}
