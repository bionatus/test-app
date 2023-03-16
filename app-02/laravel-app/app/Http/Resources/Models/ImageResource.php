<?php

namespace App\Http\Resources\Models;

use App\Constants\MediaConversionNames;
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

    public function toArray($request): array
    {
        return [
            'id'          => $this->resource->uuid,
            'url'         => $this->resource->getUrl(),
            'conversions' => [
                'thumb' => $this->when($this->resource->hasGeneratedConversion(MediaConversionNames::THUMB),
                    fn() => $this->resource->getUrl(MediaConversionNames::THUMB)),
            ],
        ];
    }

    public static function jsonSchema(bool $nullable = false): array
    {
        return [
            'type'                 => $nullable ? ['object', 'null'] : ['object'],
            'properties'           => [
                'id'          => ['type' => ['string']],
                'url'         => ['type' => ['string']],
                'conversions' => [
                    'type'  => ['array', 'object'],
                    'items' => [
                        'properties' => [
                            'thumb' => ['type' => ['string']],
                        ],
                    ],
                ],
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
