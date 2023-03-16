<?php

namespace App\Http\Resources\Api\V3\Account\Wishlist;

use App;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\WishlistResource;
use App\Models\Wishlist;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Wishlist $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private WishlistResource $baseResource;

    public function __construct(Wishlist $resource)
    {
        parent::__construct($resource);
        $this->baseResource = new WishlistResource($resource);
    }

    public function toArray($request): array
    {
        $totalItems = (int) $this->resource->itemWishlists()->sum('quantity');

        $baseResource                = $this->baseResource->toArray($request);
        $baseResource['total_items'] = $totalItems;

        return $baseResource;
    }

    public static function jsonSchema(): array
    {

        return array_merge_recursive(WishlistResource::jsonSchema(), [
            'properties' => [
                'total_items' => ['type' => ['integer']],
            ],
            'required'   => [
                'total_items',
            ],
        ]);
    }
}
