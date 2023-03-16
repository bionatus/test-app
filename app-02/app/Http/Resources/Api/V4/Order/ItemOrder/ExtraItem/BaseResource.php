<?php

namespace App\Http\Resources\Api\V4\Order\ItemOrder\ExtraItem;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ItemOrderResource;
use App\Models\ItemOrder;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource implements HasJsonSchema
{
    private ItemOrderResource $itemResource;

    public function __construct(ItemOrder $resource)
    {
        parent::__construct($resource);
        $this->itemResource = new ItemOrderResource($resource);
    }

    public function toArray($request)
    {
        $item = $this->resource->item;

        $response = $this->itemResource->toArray($request);

        $response['item'] = new ItemResource($item);

        return $response;
    }

    public static function jsonSchema(): array
    {
        $schema = ItemOrderResource::jsonSchema();

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
