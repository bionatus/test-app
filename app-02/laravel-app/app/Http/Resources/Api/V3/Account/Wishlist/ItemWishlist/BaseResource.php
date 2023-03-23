<?php

namespace App\Http\Resources\Api\V3\Account\Wishlist\ItemWishlist;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ItemResource;
use App\Http\Resources\Models\ItemWishlistResource;
use App\Models\ItemWishlist;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource implements HasJsonSchema
{
    private ItemWishlistResource $itemResource;

    public function __construct(ItemWishlist $resource)
    {
        parent::__construct($resource);
        $this->itemResource = new ItemWishlistResource($resource);
    }

    public function toArray($request)
    {
        $item = $this->resource->item;

        $response         = $this->itemResource->toArray($request);
        $response['item'] = new ItemResource($item);

        return $response;
    }

    public static function jsonSchema(): array
    {
        $schema = ItemWishListResource::jsonSchema();

        return array_merge_recursive($schema, [
            'properties' => [
                'item' => ItemResource::jsonSchema(),
            ],
            'required'   => [
                'item',
            ],
        ]);
    }
}
