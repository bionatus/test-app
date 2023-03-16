<?php

namespace App\Http\Resources\LiveApi\V1\Order;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OrderDeliveryResource as BaseResource;
use App\Models\OrderDelivery;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property OrderDelivery $resource
 */
class OrderDeliveryResource extends JsonResource implements HasJsonSchema
{
    private BaseResource $baseResource;

    public function __construct(OrderDelivery $resource)
    {
        parent::__construct($resource);
        $this->baseResource = new BaseResource($resource);
    }

    public function toArray($request): array
    {
        return $this->baseResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return BaseResource::jsonSchema();
    }
}
