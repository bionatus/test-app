<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\CartItem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property CartItem $resource
 */
class CartItemResource extends JsonResource implements HasJsonSchema
{
    public function __construct(CartItem $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        return [
            'id'       => $this->resource->getRouteKey(),
            'quantity' => (int) $this->resource->quantity,
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
