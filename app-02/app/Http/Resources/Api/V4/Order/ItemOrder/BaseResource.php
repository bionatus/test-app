<?php

namespace App\Http\Resources\Api\V4\Order\ItemOrder;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\GenericReplacementResource;
use App\Http\Resources\Models\ItemOrderResource;
use App\Http\Resources\Models\ItemResource;
use App\Http\Resources\Models\ReplacementResource;
use App\Models\ItemOrder;
use App\Models\Order;
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
        $item               = $this->resource->item;
        $replacement        = $this->resource->replacement;
        $genericReplacement = $this->resource->generic_part_description;

        $replacementResource        = $replacement ? new ReplacementResource($replacement) : null;
        $genericReplacementResource = $genericReplacement ? new GenericReplacementResource($genericReplacement) : null;

        $response   = $this->itemResource->toArray($request);
        $order      = $this->resource->order;
        $hideNumber = false;

        if ($order->isPending() || $order->isPendingApproval()) {
            $hideNumber = true;
        }
        $response['item']        = new ItemResource($item, $hideNumber);
        $response['replacement'] = $replacementResource ?? $genericReplacementResource;

        return $response;
    }

    public static function jsonSchema(): array
    {
        $schema = ItemOrderResource::jsonSchema();

        return array_merge_recursive($schema, [
            'properties' => [
                'item'        => ItemResource::jsonSchema(),
                'replacement' => [
                    'oneOf' => [
                        ReplacementResource::jsonSchema(),
                        GenericReplacementResource::jsonSchema(),
                        ['type' => 'null'],
                    ],
                ],
            ],
            'required'   => [
                'item',
                'replacement',
            ],
        ]);
    }
}
