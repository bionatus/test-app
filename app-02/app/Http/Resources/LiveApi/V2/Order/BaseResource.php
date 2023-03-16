<?php

namespace App\Http\Resources\LiveApi\V2\Order;

use App;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\CompanyResource;
use App\Http\Resources\Models\OrderResource;
use App\Http\Resources\Models\OrderSubstatusResource;
use App\Http\Resources\Models\SupplierUserResource;
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
        $order = $this->resource;
        $user  = $order->user;

        $baseResource = $this->baseResource->toArray($request);

        $supplierUser = $order->user?->supplierUsers?->first();

        $staff   = $order->lastOrderStaff?->staff;
        $company = $order->company;

        return array_replace_recursive($baseResource, [
            'had_truck_stock'  => $order->extra_items_added_later,
            'total_line_items' => $order->total_line_items,
            'bid_number'       => $order->bid_number,
            'total'            => $order->total,
            'paid_total'       => $order->paid_total,
            'current_status'   => new OrderSubstatusResource($order->lastStatus),
            'user'             => $user ? new UserResource($user) : new UserDeletedResource($order->orderLockedData),
            'supplier_user'    => $supplierUser ? new SupplierUserResource($supplierUser) : null,
            'items'            => ItemResource::collection($order->items),
            'working_on_it'    => $staff ? new StaffResource($staff) : null,
            'company'          => $company ? new CompanyResource($company) : null,
        ]);
    }

    public static function jsonSchema(): array
    {
        $orderResourceSchema = OrderResource::jsonSchema();

        $supplierUserResource         = SupplierUserResource::jsonSchema();
        $supplierUserResource['type'] = ['object', 'null'];

        return array_replace_recursive($orderResourceSchema, [
            'properties' => [
                'had_truck_stock'  => ['type' => ['boolean']],
                'total_line_items' => ['type' => ['number']],
                'bid_number'       => ['type' => ['string', 'null']],
                'total'            => ['type' => ['number', 'null']],
                'paid_total'       => ['type' => ['number', 'null']],
                'current_status'   => OrderSubstatusResource::jsonSchema(),
                'user'             => ['oneOf' => [UserResource::jsonSchema(), UserDeletedResource::jsonSchema()]],
                'supplier_user'    => $supplierUserResource,
                'items'            => ItemResource::jsonSchema(),
                'working_on_it'    => StaffResource::jsonSchema(),
                'company'          => CompanyResource::jsonSchema(),
            ],
            'required'   => [
                'had_truck_stock',
                'total_line_items',
                'bid_number',
                'total',
                'paid_total',
                'current_status',
                'user',
                'supplier_user',
                'items',
                'company',
            ],
        ]);
    }
}
