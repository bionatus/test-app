<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\CustomItem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property CustomItem $resource
 */
class CustomItemResource extends JsonResource implements HasJsonSchema
{
    public function __construct(CustomItem $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        return [
            'id'      => $this->resource->item->getRouteKey(),
            'name'    => $this->resource->name,
            'creator' => $this->resource->creator_type,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'      => ['type' => ['string']],
                'name'    => ['type' => ['string']],
                'creator' => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'id',
                'name',
                'creator',
            ],
            'additionalProperties' => false,
        ];
    }
}
