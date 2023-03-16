<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\ItemOrder;
use Illuminate\Http\Resources\Json\JsonResource;

class GenericReplacementResource extends JsonResource implements HasJsonSchema
{
    public function __construct(string $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        return [
            'type'        => ItemOrder::REPLACEMENT_TYPE_GENERIC,
            'description' => $this->resource,
        ];
    }

    public static function jsonSchema($part = true): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'type'        => ['type' => ['string']],
                'description' => ['type' => ['string']],
            ],
            'required'             => [
                'type',
                'description',
            ],
            'additionalProperties' => false,
        ];
    }
}
