<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\CustomItem;
use App\Models\Item;
use App\Models\Part;
use App\Models\Supply;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Item $resource
 */
class ItemResource extends JsonResource implements HasJsonSchema
{
    private bool $hideNumber;

    public function __construct(Item $resource, bool $hideNumber = false)
    {
        parent::__construct($resource);

        $this->hideNumber = $hideNumber;
    }

    public function toArray($request): array
    {
        $item = $this->resource;
        /** @var Part|Supply|CustomItem $orderable */
        $orderable = $item->orderable;

        $response = [
            'id'   => $item->getRouteKey(),
            'type' => $item->type,
        ];

        if ($item->isPart()) {
            $response['info'] = new PartResource($orderable, $this->hideNumber);
        }

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
                        PartResource::jsonSchema(),
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
