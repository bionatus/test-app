<?php

namespace App\Http\Resources\Api\V2\Tag;

use App\Http\Resources\HasJsonSchema;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ?string $resource
 */
class SeriesImageResource extends JsonResource implements HasJsonSchema
{
    public function __construct(?string $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'id'          => $this->resource,
            'url'         => $this->resource,
            'conversions' => [],
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'          => ['type' => ['string', 'null']],
                'url'         => ['type' => ['string', 'null']],
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
