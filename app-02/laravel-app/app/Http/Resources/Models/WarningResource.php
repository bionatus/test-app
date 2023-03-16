<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\Warning;
use Illuminate\Http\Resources\Json\JsonResource;

class WarningResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Warning $warning)
    {
        parent::__construct($warning);
    }

    public function toArray($request)
    {
        return [
            'id'          => $this->resource->getRouteKey(),
            'title'       => $this->resource->title,
            'description' => $this->resource->description,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'          => ['type' => ['integer']],
                'title'       => ['type' => ['string']],
                'description' => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'title',
                'description',
            ],
            'additionalProperties' => false,
        ];
    }
}
