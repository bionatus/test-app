<?php

namespace App\Http\Resources\LiveApi\V2\Order\ItemOrder\ExtraItem;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\CustomItemResource;
use App\Http\Resources\Models\ItemOrderResource;
use App\Http\Resources\Models\SupplyResource as BaseSupplyResource;
use App\Models\Item;
use App\Models\ItemOrder;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource implements HasJsonSchema
{
    private ItemOrderResource $itemOrderResource;

    public function __construct(ItemOrder $resource)
    {
        parent::__construct($resource);
        $this->itemOrderResource = new ItemOrderResource($resource);
    }

    public function toArray($request)
    {
        $item     = $this->resource->item;
        $response = $this->itemOrderResource->toArray($request);

        if ($item->type == Item::TYPE_SUPPLY) {
            $response['item'] = new BaseSupplyResource($item->orderable);
        }
        if ($item->type == Item::TYPE_CUSTOM_ITEM) {
            $response['item'] = new CustomItemResource($item->orderable);
        }

        return $response;
    }

    public static function jsonSchema($part = true): array
    {
        $schema = ItemOrderResource::jsonSchema();

        return array_merge_recursive($schema, [
            'properties' => [
                'item' => [
                    'anyOf' => [
                        BaseSupplyResource::jsonSchema(),
                        CustomItemResource::jsonSchema(),
                    ],
                ],
            ],
            'required'   => [
                'item',
            ],
        ]);
    }
}
