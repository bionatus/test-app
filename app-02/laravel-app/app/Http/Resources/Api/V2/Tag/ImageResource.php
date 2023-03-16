<?php

namespace App\Http\Resources\Api\V2\Tag;

use App\Http\Resources\HasJsonSchema;
use App\Models\Media;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Media $resource
 */
class ImageResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Media $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'id'          => $this->resource->uuid,
            'url'         => $this->resource->getUrl(),
            'conversions' => [],
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'          => ['type' => ['string']],
                'url'         => ['type' => ['string']],
                'conversions' => ['type' => ['object', 'array']],
            ],
            'required'             => [
                'id',
                'url',
                'conversions',
            ],
            'additionalProperties' => false,
        ];
    }
}
