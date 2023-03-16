<?php

namespace App\Http\Resources\Models\Brand;

use App\Http\Resources\HasJsonSchema;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource implements HasJsonSchema
{
    public function __construct(array $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        if (empty($this->resource[0])) {
            return null;
        };

        $logo = $this->resource[0];

        $thumbnail = !empty($logo['thumbnails']['full']['url']) ? $logo['thumbnails']['full']['url'] : null;

        return [
            'id'          => $logo['id'] ?? '',
            'url'         => $logo['url'] ?? '',
            'conversions' => [
                'thumb' => $this->when(!is_null($thumbnail), $thumbnail),
            ],
        ];
    }

    public static function jsonSchema(bool $nullable = false): array
    {
        return [
            'type'                 => $nullable ? ['object', 'array', 'null'] : ['object', 'array'],
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
