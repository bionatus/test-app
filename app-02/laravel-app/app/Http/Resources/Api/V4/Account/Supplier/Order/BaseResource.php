<?php

namespace App\Http\Resources\Api\V4\Account\Supplier\Order;

use App;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OrderResource;
use App\Http\Resources\Models\OrderSubstatusResource;
use App\Http\Resources\Models\StaffResource;
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
        $order          = $this->resource;
        $totalLineItems = $order->activeItemOrders()->count();
        $baseResource   = $this->baseResource->toArray($request);
        $staff          = $order->lastOrderStaff?->staff;

        return array_replace_recursive($baseResource, [
            'current_status'   => new OrderSubstatusResource($order->lastStatus),
            'total_line_items' => $totalLineItems,
            'points_earned'    => $order->totalPointsEarned(),
            'working_on_it'    => $staff ? new StaffResource($staff) : null,
        ]);
    }

    public static function jsonSchema(): array
    {
        return array_replace_recursive(OrderResource::jsonSchema(), [
            'properties' => [
                'current_status'   => OrderSubstatusResource::jsonSchema(),
                'total_line_items' => ['type' => ['integer']],
                'points_earned'    => ['type' => ['integer']],
                'working_on_it'    => StaffResource::jsonSchema(),
            ],
            'required'   => [
                'current_status',
                'total_line_items',
                'points_earned',
            ],
        ]);
    }
}
