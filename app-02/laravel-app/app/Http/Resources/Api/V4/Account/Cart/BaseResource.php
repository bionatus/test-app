<?php

namespace App\Http\Resources\Api\V4\Account\Cart;

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
        $supplier   = $cart->supplier;
        $totalItems = (int) $cart->cartItems()->sum('quantity');

        $baseResource = $this->baseResource->toArray($request);

        return array_replace_recursive($baseResource, [
            'total_items' => $totalItems,
            'supplier'    => $supplier ? new SupplierResource($supplier) : null,
        ]);
    }

    public static function jsonSchema(): array
    {
        $supplierSchema           = SupplierResource::jsonSchema();
        $supplierSchema['type'][] = 'null';

        return array_replace_recursive(CartResource::jsonSchema(), [
            'properties' => [
                'total_items' => ['type' => ['integer']],
                'supplier'    => $supplierSchema,
            ],
            'required'   => [
                'total_items',
                'supplier',
            ],
        ]);
    }
}
