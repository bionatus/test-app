<?php

namespace App\Http\Resources\LiveApi\V2\Order;

use App;
use App\Actions\Models\PubnubChannel\GetChannelByOrder;
use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\Order\InvoiceResource;
use App\Http\Resources\Models\OrderResource;
use App\Http\Resources\Models\OrderSubstatusResource;
use App\Http\Resources\Models\SupplierUserResource;
use App\Http\Resources\Models\UserDeletedResource;
use App\Models\ItemOrder\Scopes\ByInitialRequest;
use App\Models\Order;
use App\Models\Scopes\BySupplier;
use App\Models\SupplierUser;
use Illuminate\Http\Resources\Json\JsonResource;

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

    public function toArray($request)
    {
        $order    = $this->resource;
        $user     = $order->user;
        $supplier = $order->supplier;

        $baseResource = $this->baseResource->toArray($request);

        $hadTruckStock = $order->items()->scoped(new ByInitialRequest(false))->exists();

        /** @var App\Models\Media $invoice */
        $invoice = $order->getFirstMedia(MediaCollectionNames::INVOICE);

        $pubnubChannel = App::make(GetChannelByOrder::class, ['order' => $order])->execute();

        /** @var SupplierUser $supplierUser */
        $supplierUser = $user?->supplierUsers()->scoped(new BySupplier($supplier))->first();

        $staff                         = $order->lastOrderStaff?->staff;
        $baseResource['working_on_it'] = $staff ? new StaffResource($staff) : null;

        return array_merge_recursive($baseResource, [
            'had_truck_stock'  => $hadTruckStock,
            'total_line_items' => $order->itemOrders()->count(),
            'bid_number'       => $order->bid_number,
            'total'            => $order->total,
            'paid_total'       => $order->paid_total,
            'note'             => $order->note,
            'channel'          => $pubnubChannel,
            'current_status'   => new OrderSubstatusResource($order->lastStatus),
            'user'             => $user ? new UserResource($user) : new UserDeletedResource($order->orderLockedData),
            'supplier_user'    => $supplierUser ? new SupplierUserResource($supplierUser) : null,
            'invoice'          => $invoice ? new InvoiceResource($invoice) : null,
        ]);
    }

    public static function jsonSchema(): array
    {
        $orderResourceSchema                                = OrderResource::jsonSchema();
        $orderResourceSchema['properties']['working_on_it'] = StaffResource::jsonSchema();

        $supplierUserResource         = SupplierUserResource::jsonSchema();
        $supplierUserResource['type'] = ['object', 'null'];

        return array_merge_recursive($orderResourceSchema, [
            'properties' => [
                'had_truck_stock'  => ['type' => ['boolean']],
                'total_line_items' => ['type' => ['number']],
                'bid_number'       => ['type' => ['string', 'null']],
                'total'            => ['type' => ['number', 'null']],
                'paid_total'       => ['type' => ['number', 'null']],
                'note'             => ['type' => ['string', 'null']],
                'channel'          => ['type' => ['string']],
                'current_status'   => OrderSubstatusResource::jsonSchema(),
                'user'             => ['oneOf' => [UserResource::jsonSchema(), UserDeletedResource::jsonSchema()]],
                'supplier_user'    => $supplierUserResource,
                'invoice'          => InvoiceResource::jsonSchema(),
            ],
            'required'   => [
                'had_truck_stock',
                'total_line_items',
                'bid_number',
                'total',
                'paid_total',
                'note',
                'channel',
                'current_status',
                'user',
                'supplier_user',
                'invoice',
            ],
        ]);
    }
}
