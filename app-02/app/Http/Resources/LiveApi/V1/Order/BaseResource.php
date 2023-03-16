<?php

namespace App\Http\Resources\LiveApi\V1\Order;

use App;
use App\Actions\Models\PubnubChannel\GetChannelByOrder;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OrderResource;
use App\Http\Resources\Models\UserDeletedResource;
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

    public function toArray($request)
    {
        $order         = $this->resource;
        $baseResource  = $this->baseResource->toArray($request);
        $pubnubChannel = App::make(GetChannelByOrder::class, ['order' => $order])->execute();
        $user          = $order->user;

        return array_merge_recursive($baseResource, [
            'status'     => $order->getStatusName(),
            'total'      => $order->subTotal(),
            'user'       => $user ? new UserResource($user) : new UserDeletedResource($order->orderLockedData),
            'bid_number' => $order->bid_number,
            'discount'   => $order->discount,
            'tax'        => $order->tax,
            'channel'    => $pubnubChannel,
        ]);
    }

    public static function jsonSchema(): array
    {
        return array_merge_recursive(OrderResource::jsonSchema(), [
            'properties' => [
                'status'     => ['type' => ['string']],
                'total'      => ['type' => ['number']],
                'user'       => ['oneOf' => [UserResource::jsonSchema(), UserDeletedResource::jsonSchema()]],
                'bid_number' => ['type' => ['string', 'null']],
                'discount'   => ['type' => ['number']],
                'tax'        => ['type' => ['number']],
                'channel'    => ['type' => ['string']],
            ],
            'required'   => [
                'status',
                'total',
                'user',
                'bid_number',
                'discount',
                'tax',
                'channel',
            ],
        ]);
    }
}
