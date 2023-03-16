<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\ItemWishList;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ItemWishList $resource
 */
class ItemWishlistResource extends JsonResource implements HasJsonSchema
{
    public function __construct(ItemWishList $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        return [
            'id'       => $this->resource->getRouteKey(),
            'quantity' => $this->resource->quantity,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'       => ['type' => ['string']],
                'quantity' => ['type' => ['integer']],
            ],
            'required'             => [
                'id',
                'quantity',
            ],
            'additionalProperties' => false,
        ];
    }
}
