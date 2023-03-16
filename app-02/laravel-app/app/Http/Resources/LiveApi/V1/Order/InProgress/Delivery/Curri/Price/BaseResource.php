<?php

namespace App\Http\Resources\LiveApi\V1\Order\InProgress\Delivery\Curri\Price;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OrderDeliveryResource;
use App\Models\OrderDelivery;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property OrderDelivery $resource */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private OrderDeliveryResource $baseResource;

    public function __construct(OrderDelivery $resource)
    {
        parent::__construct($resource);

        $this->baseResource = new OrderDeliveryResource($resource);
    }

    public function toArray($request): array
    {
        return $this->baseResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return OrderDeliveryResource::jsonSchema();
    }
}
