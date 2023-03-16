<?php

namespace App\Http\Resources\Api\V2\Tag;

use App\Http\Resources\HasJsonSchema;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandImageResource extends JsonResource implements HasJsonSchema
{
    public function toArray($request)
    {
        $id  = is_array($this->resource) ? ($this->resource['id'] ?? null) : null;
        $url = is_array($this->resource) ? ($this->resource['url'] ?? null) : null;

        return [
            'id'          => $id ?? $url,
            'url'         => $url,
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
                'conversions' => ['type' => ['array']],
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
