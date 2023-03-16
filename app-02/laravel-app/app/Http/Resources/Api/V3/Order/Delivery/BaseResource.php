<?php

namespace App\Http\Resources\Api\V3\Order\Delivery;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OrderDeliveryResource;
use App\Models\OrderDelivery;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property OrderDelivery $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private OrderDeliveryResource $orderDeliveryResource;

    public function __construct(OrderDelivery $resource)
    {
        parent::__construct($resource);
        $this->orderDeliveryResource = new OrderDeliveryResource($resource);
    }

    public function toArray($request): array
    {
        return $this->orderDeliveryResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return OrderDeliveryResource::jsonSchema();
    }
}
