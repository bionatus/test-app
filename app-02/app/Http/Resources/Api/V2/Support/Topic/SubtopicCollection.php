<?php

namespace App\Http\Resources\Api\V2\Support\Topic;

use App\Http\Resources\HasJsonSchema;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SubtopicCollection extends ResourceCollection implements HasJsonSchema
{
    public function toArray($request)
    {
        return [
            'data' => SubtopicResource::collection($this->collection),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'data' => [
                    'type'  => ['array'],
                    'items' => SubtopicResource::jsonSchema(),
                ],
            ],
            'required'             => [
                'data',
            ],
            'additionalProperties' => false,
        ];
    }
}
