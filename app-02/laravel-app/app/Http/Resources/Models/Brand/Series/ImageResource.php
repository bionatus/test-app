<?php

namespace App\Http\Resources\Models\Brand\Series;

use App\Http\Resources\HasJsonSchema;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ImageResource extends JsonResource implements HasJsonSchema
{
    public function __construct(?string $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'id'          => Str::uuid()->toString(),
            'url'         => $this->resource,
            'conversions' => [],
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object', 'null'],
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
