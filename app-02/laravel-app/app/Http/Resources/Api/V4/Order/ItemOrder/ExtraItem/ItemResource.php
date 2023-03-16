<?php

namespace App\Http\Resources\Api\V4\Order\ItemOrder\ExtraItem;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\CustomItemResource;
use App\Http\Resources\Models\SupplyResource;
use App\Models\CustomItem;
use App\Models\Item;
use App\Models\Supply;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Item $resource
 */
class ItemResource extends JsonResource implements HasJsonSchema
{
    public function toArray($request): array
    {
        $item = $this->resource;
        /** @var Supply|CustomItem $orderable */
        $orderable = $item->orderable;

        $response = [
            'id'   => $item->getRouteKey(),
            'type' => $item->type,
        ];

        if ($item->isSupply()) {
            $response['info'] = new SupplyResource($orderable);
        }

        if ($item->isCustomItem()) {
            $response['info'] = new CustomItemResource($orderable);
        }

        return $response;
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'   => ['type' => ['string']],
                'type' => ['type' => ['string']],
                'info' => [
                    'oneOf' => [
                        SupplyResource::jsonSchema(),
                        CustomItemResource::jsonSchema(),
                    ],
                ],
            ],
            'required'             => [
                'id',
                'type',
                'info',
            ],
            'additionalProperties' => false,
        ];
    }
}
