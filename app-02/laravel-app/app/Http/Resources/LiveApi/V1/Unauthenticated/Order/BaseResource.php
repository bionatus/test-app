<?php

namespace App\Http\Resources\LiveApi\V1\Unauthenticated\Order;

use App\Http\Resources\HasJsonSchema;
use App\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Order $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Order $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $order      = $this->resource;
        $totalItems = (int) $order->availableItemOrders()->sum('quantity');

        /* TODO change StatusResource */

        return [
            'id'          => $order->getRouteKey(),
            'status'      => $order->getStatusName(),
            'bid_number'  => $order->bid_number,
            'supplier'    => $order->supplier ? new SupplierResource($order->supplier) : null,
            'delivery'    => new OrderDeliveryResource($order->orderDelivery),
            'total_items' => $totalItems,
            'discount'    => $order->discount,
            'tax'         => $order->tax,
            'total'       => $order->subTotal(),
        ];
    }

    public static function jsonSchema(): array
    {
        /* TODO change StatusResource */

        return [
            'properties'           => [
                'id'          => ['type' => ['string']],
                'status'      => ['type' => ['string']],
                'bid_number'  => ['type' => ['string', 'null']],
                'supplier'    => SupplierResource::jsonSchema(),
                'delivery'    => OrderDeliveryResource::jsonSchema(),
                'total_items' => ['type' => ['integer']],
                'discount'    => ['type' => ['number']],
                'tax'         => ['type' => ['number']],
                'total'       => ['type' => ['number']],
            ],
            'required'             => [
                'id',
                'status',
                'bid_number',
                'supplier',
                'delivery',
                'total_items',
                'discount',
                'tax',
                'total',
            ],
            'additionalProperties' => false,
        ];
    }
}
