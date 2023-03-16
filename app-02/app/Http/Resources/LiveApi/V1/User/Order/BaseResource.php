<?php

namespace App\Http\Resources\LiveApi\V1\User\Order;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OrderResource;
use App\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property Order $resource */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private OrderResource $baseResource;

    public function __construct(Order $resource)
    {
        parent::__construct($resource);

        $this->baseResource = new OrderResource($resource);
    }

    public function toArray($request)
    {
        $order        = $this->resource;
        $baseResource = $this->baseResource->toArray($request);
        $totalItems   = (int) $order->itemOrders->sum('quantity');

        return array_replace_recursive($baseResource, [
            'oem'         => $order->oem ? new OemResource($this->resource->oem) : null,
            'status'      => $order->getStatusName(),
            'total'       => $order->subTotal(),
            'user'        => new UserResource($order->user),
            'bid_number'  => $order->bid_number,
            'discount'    => $order->discount,
            'tax'         => $order->tax,
            'total_items' => $totalItems,
        ]);
    }

    public static function jsonSchema(): array
    {
        return array_replace_recursive(OrderResource::jsonSchema(), [
            'properties' => [
                'oem'         => OemResource::jsonSchema(),
                'status'      => ['type' => ['string']],
                'total'       => ['type' => ['number']],
                'user'        => UserResource::jsonSchema(),
                'bid_number'  => ['type' => ['string', 'null']],
                'discount'    => ['type' => ['number']],
                'tax'         => ['type' => ['number']],
                'total_items' => ['type' => ['integer']],
            ],
            'required'   => [
                'oem',
                'status',
                'total',
                'user',
                'bid_number',
                'discount',
                'tax',
                'total_items',
            ],
        ]);
    }
}
