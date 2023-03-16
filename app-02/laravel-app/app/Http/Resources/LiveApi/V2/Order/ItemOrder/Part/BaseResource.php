<?php

namespace App\Http\Resources\LiveApi\V2\Order\ItemOrder\Part;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\GenericReplacementResource;
use App\Http\Resources\Models\ItemOrderResource;
use App\Http\Resources\Models\PartResource as BasePartResource;
use App\Http\Resources\Models\ReplacementResource;
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
        $item               = $this->resource->item;
        $replacement        = $this->resource->replacement;
        $genericReplacement = $this->resource->generic_part_description;

        $replacementResource        = $replacement ? new ReplacementResource($replacement) : null;
        $genericReplacementResource = $genericReplacement ? new GenericReplacementResource($genericReplacement) : null;

        $response                = $this->itemOrderResource->toArray($request);
        $response['item']        = new BasePartResource($item->part);
        $response['replacement'] = $replacementResource ?? $genericReplacementResource;

        return $response;
    }

    public static function jsonSchema($part = true): array
    {
        $schema = ItemOrderResource::jsonSchema();

        return array_merge_recursive($schema, [
            'properties' => [
                'item'        => BasePartResource::jsonSchema(),
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
