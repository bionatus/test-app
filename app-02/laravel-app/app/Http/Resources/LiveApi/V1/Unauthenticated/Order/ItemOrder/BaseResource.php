<?php

namespace App\Http\Resources\LiveApi\V1\Unauthenticated\Order\ItemOrder;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\GenericReplacementResource;
use App\Http\Resources\Models\ItemOrderResource;
use App\Http\Resources\Models\ItemResource;
use App\Http\Resources\Models\ReplacementResource;
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
        $item               = $this->resource->item;
        $replacement        = $this->resource->replacement;
        $genericReplacement = $this->resource->generic_part_description;

        $replacementResource        = $replacement ? new ReplacementResource($replacement) : null;
        $genericReplacementResource = $genericReplacement ? new GenericReplacementResource($genericReplacement) : null;

        $response                = $this->itemResource->toArray($request);
        $response['item']        = new ItemResource($item);
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
