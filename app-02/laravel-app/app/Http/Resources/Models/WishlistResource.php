<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\Wishlist;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Wishlist $resource
 */
class WishlistResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Wishlist $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        return [
            'id'   => $this->resource->getRouteKey(),
            'name' => $this->resource->name,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'   => ['type' => ['string']],
                'name' => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'name',
            ],
            'additionalProperties' => false,
        ];
    }
}
