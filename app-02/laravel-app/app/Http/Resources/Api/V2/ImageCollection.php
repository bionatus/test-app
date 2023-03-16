<?php

namespace App\Http\Resources\Api\V2;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ImageResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ImageCollection extends ResourceCollection implements HasJsonSchema
{
    public function toArray($request): array
    {
        return [
            'data' => ImageResource::collection($this->collection),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'data'  => ['type' => ['array']],
                'items' => ImageResource::jsonSchema(),
            ],
            'required'             => [
                'data',
            ],
            'additionalProperties' => false,
        ];
    }
}
