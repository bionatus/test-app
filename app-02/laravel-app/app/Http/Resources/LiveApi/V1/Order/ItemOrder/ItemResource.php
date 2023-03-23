<?php

namespace App\Http\Resources\LiveApi\V1\Order\ItemOrder;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\CustomItemResource;
use App\Http\Resources\Models\ItemResource as ModelItemResource;
use App\Http\Resources\Models\SupplyResource;
use App\Models\Item;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource implements HasJsonSchema
{
    private ModelItemResource $itemResource;

    public function __construct(Item $resource)
    {
        parent::__construct($resource);
        $this->itemResource = new ModelItemResource($resource);
    }

    public function toArray($request): array
    {
        $response = $this->itemResource->toArray($request);

        if ($this->resource->isPart()) {
            $response['info'] = new PartResource($this->resource->part);
        }

        return $response;
    }

    public static function jsonSchema(): array
    {
        $schema                                = ModelItemResource::jsonSchema();
        $schema['properties']['info']['oneOf'] = [
            PartResource::jsonSchema(),
            SupplyResource::jsonSchema(),
            CustomItemResource::jsonSchema(),
        ];

        return $schema;
    }
}
