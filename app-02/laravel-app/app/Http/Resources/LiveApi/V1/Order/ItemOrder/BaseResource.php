<?php

namespace App\Http\Resources\LiveApi\V1\Order\ItemOrder;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\GenericReplacementResource;
use App\Http\Resources\Models\ItemOrderResource;
use App\Http\Resources\Models\ReplacementResource;
use App\Models\ItemOrder;
use Auth;
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
        $user               = Auth::user();
        $item               = $this->resource->item;
        $replacement        = $this->resource->replacement;
        $genericReplacement = $this->resource->generic_part_description;

        $replacementResource        = $replacement ? new ReplacementResource($replacement) : null;
        $genericReplacementResource = $genericReplacement ? new GenericReplacementResource($genericReplacement) : null;

        $response                         = $this->itemOrderResource->toArray($request);
        $response['authorized_to_delete'] = $user->can('delete', $this->resource);
        $response['item']                 = new ItemResource($item);
        $response['replacement']          = $replacementResource ?? $genericReplacementResource;

        return $response;
    }

    public static function jsonSchema($part = true): array
    {
        $schema = ItemOrderResource::jsonSchema();

        return array_merge_recursive($schema, [
            'properties' => [
                'authorized_to_delete' => ['type' => ['boolean']],
                'item'                 => ItemResource::jsonSchema(),
                'replacement'          => [
                    'oneOf' => [
                        ReplacementResource::jsonSchema(),
                        GenericReplacementResource::jsonSchema(),
                        ['type' => 'null'],
                    ],
                ],
            ],
            'required'   => [
                'authorized_to_delete',
                'item',
                'replacement',
            ],
        ]);
    }
}
