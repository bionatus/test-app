<?php

namespace App\Http\Resources\Api\V2\Post;

use App\Http\Resources\Api\V2\Tag\BaseResource;
use App\Http\Resources\HasJsonSchema;
use App\Models\Tag;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TagCollection extends ResourceCollection implements HasJsonSchema
{
    public function toArray($request)
    {
        $taggableTypes = $this->collection->map(function (Tag $tag) {
            return $tag->taggable->toTagType();
        });

        return [
            'data' => BaseResource::collection($taggableTypes),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'data' => [
                    'type'  => ['array'],
                    'items' => BaseResource::jsonSchema(),
                ],
            ],
            'required'             => [
                'data',
            ],
            'additionalProperties' => false,
        ];
    }
}
