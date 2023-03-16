<?php

namespace App\Http\Resources\Api\V3\Order;

use App;
use App\Actions\Models\Order\CalculatePoints;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OrderResource;
use App\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;
use Throwable;

/**
 * @property Order $resource
 */
class DetailedResource extends JsonResource implements HasJsonSchema
{
    private OrderResource $baseResource;

    public function __construct(Order $resource)
    {
        parent::__construct($resource);
        $this->baseResource = new OrderResource($resource);
    }

    /**
     * @throws Throwable
     */
    public function toArray($request): array
    {
        $order              = $this->resource;
        $totalItemsQuantity = (int) $order->activeItemOrders()->sum('quantity');
        $pointData          = (App::make(CalculatePoints::class, ['order' => $order]))->execute();
        $baseResource       = $this->baseResource->toArray($request);

        return array_merge_recursive($baseResource, [
            'status'               => $order->getStatusName(),
            'total'                => $order->subTotal(),
            'supplier'             => $order->supplier ? new SupplierResource($order->supplier) : null,
            'total_items_quantity' => $totalItemsQuantity,
            'bid_number'           => $order->bid_number,
            'discount'             => $order->discount,
            'tax'                  => $order->tax,
            'points'               => $pointData->points(),
        ]);
    }

    public static function jsonSchema(): array
    {
        $supplierResource           = SupplierResource::jsonSchema();
        $supplierResource['type'][] = 'null';

        return array_merge_recursive(OrderResource::jsonSchema(), [
            'properties' => [
                'status'               => ['type' => ['string']],
                'total'                => ['type' => ['number']],
                'supplier'             => $supplierResource,
                'total_items_quantity' => ['type' => ['integer']],
                'bid_number'           => ['type' => ['string', 'null']],
                'discount'             => ['type' => ['number']],
                'tax'                  => ['type' => ['number']],
                'points'               => ['type' => ['number']],
            ],
            'required'   => [
                'status',
                'total',
                'supplier',
                'total_items_quantity',
                'bid_number',
                'discount',
                'tax',
                'points',
            ],
        ]);
    }
}
