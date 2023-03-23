<?php

namespace App\Http\Resources\Api\V3\Account\Cart;

use App;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\CartResource;
use App\Models\Cart;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Cart $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private CartResource $baseResource;

    public function __construct(Cart $resource)
    {
        parent::__construct($resource);
        $this->baseResource = new CartResource($resource);
    }

    public function toArray($request): array
    {
        $cart       = $this->resource;
        $totalItems = (int) $cart->cartItems()->sum('quantity');

        $baseResource = $this->baseResource->toArray($request);

        return array_replace_recursive($baseResource, [
            'total_items' => $totalItems,
        ]);
    }

    public static function jsonSchema(): array
    {
        return array_replace_recursive(CartResource::jsonSchema(), [
            'properties' => [
                'total_items' => ['type' => ['integer']],
            ],
            'required'   => [
                'total_items',
            ],
        ]);
    }
}
