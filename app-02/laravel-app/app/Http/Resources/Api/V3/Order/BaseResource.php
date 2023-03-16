<?php

namespace App\Http\Resources\Api\V3\Order;

use App;
use App\Actions\Models\PubnubChannel\GetChannelByOrder;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OrderResource;
use App\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Order $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private OrderResource $baseResource;

    public function __construct(Order $resource)
    {
        parent::__construct($resource);
        $this->baseResource = new OrderResource($resource);
    }

    public function toArray($request): array
    {
        $order         = $this->resource;
        $totalItems    = (int) $order->activeItemOrders()->sum('quantity');
        $pubnubChannel = App::make(GetChannelByOrder::class, ['order' => $order])->execute();
        $baseResource  = $this->baseResource->toArray($request);

        return array_merge_recursive($baseResource, [
            'status'        => $order->getStatusName(),
            'status_detail' => $order->lastStatus->detail,
            'supplier'      => new SupplierResource($order->supplier),
            'total_items'   => $totalItems,
            'channel'       => $pubnubChannel,
        ]);
    }

    public static function jsonSchema(): array
    {
        return array_merge_recursive(OrderResource::jsonSchema(), [
            'properties' => [
                'status'        => ['type' => ['string']],
                'status_detail' => ['type' => ['string', 'null']],
                'supplier'      => SupplierResource::jsonSchema(),
                'total_items'   => ['type' => ['integer']],
                'channel'       => ['type' => ['string']],
            ],
            'required'   => [
                'status',
                'status_detail',
                'supplier',
                'total_items',
                'channel',
            ],
        ]);
    }
}
