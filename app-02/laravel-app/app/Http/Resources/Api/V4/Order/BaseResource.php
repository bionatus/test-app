<?php

namespace App\Http\Resources\Api\V4\Order;

use App;
use App\Actions\Models\PubnubChannel\GetChannelByOrder;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OrderResource;
use App\Http\Resources\Models\OrderSubstatusResource;
use App\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Order $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private OrderResource $orderResource;

    public function __construct(Order $resource)
    {
        parent::__construct($resource);
        $this->orderResource = new OrderResource($resource);
    }

    public function toArray($request): array
    {
        $order         = $this->resource;
        $totalItems    = (int) $order->activeItemOrders()->sum('quantity');
        $pubnubChannel = App::make(GetChannelByOrder::class, ['order' => $order])->execute();
        $company       = $order->company;

        return array_replace_recursive($this->orderResource->toArray($request), [
            'current_status'  => new OrderSubstatusResource($order->lastStatus),
            'supplier'        => new SupplierResource($order->supplier),
            'total'           => $order->total,
            'paid_total'      => $order->paid_total,
            'total_items'     => $totalItems,
            'channel'         => $pubnubChannel,
            'company_account' => !!$company,
        ]);
    }

    public static function jsonSchema(): array
    {
        return array_replace_recursive(OrderResource::jsonSchema(), [
            'properties' => [
                'current_status'  => OrderSubstatusResource::jsonSchema(),
                'supplier'        => SupplierResource::jsonSchema(),
                'total'           => ['type' => ['number', 'null']],
                'paid_total'      => ['type' => ['number', 'null']],
                'total_items'     => ['type' => ['integer']],
                'channel'         => ['type' => ['string']],
                'company_account' => ['type' => ['boolean']],
            ],
            'required'   => [
                'current_status',
                'supplier',
                'total',
                'paid_total',
                'total_items',
                'channel',
                'company_account',
            ],
        ]);
    }
}
