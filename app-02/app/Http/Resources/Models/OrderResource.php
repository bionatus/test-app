<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Order $resource
 */
class OrderResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Order $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $delivery = $this->resource->orderDelivery;

        return [
            'id'            => $this->resource->getRouteKey(),
            'name'          => $this->resource->name,
            'working_on_it' => $this->resource->working_on_it,
            'created_at'    => $this->resource->created_at,
            'delivery'      => $delivery ? new OrderDeliveryResource($delivery) : null,
        ];
    }

    public static function jsonSchema(): array
    {
        $orderDelivery         = OrderDeliveryResource::jsonSchema();
        $orderDelivery['type'] = ['object', 'null'];

        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'            => ['type' => ['string']],
                'name'          => ['type' => ['string', 'null']],
                'delivery'      => $orderDelivery,
                'working_on_it' => ['type' => ['string', 'null']],
                'created_at'    => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'name',
                'delivery',
                'working_on_it',
                'created_at',
            ],
            'additionalProperties' => false,
        ];
    }
}
