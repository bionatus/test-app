<?php

namespace App\Http\Resources\Api\V4\Account\Cart\CartItem;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\CartItemResource;
use App\Http\Resources\Models\ItemResource;
use App\Models\CartItem;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource implements HasJsonSchema
{
    private CartItemResource $itemResource;

    public function __construct(CartItem $resource)
    {
        parent::__construct($resource);
        $this->itemResource = new CartItemResource($resource);
    }

    public function toArray($request)
    {
        $item = $this->resource->item;

        $response         = $this->itemResource->toArray($request);
        $response['item'] = new ItemResource($item, true);

        return $response;
    }

    public static function jsonSchema(): array
    {
        $schema = CartItemResource::jsonSchema();

        return array_replace_recursive($schema, [
            'properties' => [
                'item' => ItemResource::jsonSchema(),
            ],
            'required'   => [
                'item',
            ],
        ]);
    }
}
