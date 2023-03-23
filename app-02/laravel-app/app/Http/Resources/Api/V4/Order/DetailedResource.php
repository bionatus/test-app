<?php

namespace App\Http\Resources\Api\V4\Order;

use App;
use App\Actions\Models\Order\CalculatePoints;
use App\Actions\Models\PubnubChannel\GetChannelByOrder;
use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\Order\InvoiceResource;
use App\Http\Resources\Models\OrderResource;
use App\Http\Resources\Models\OrderSubstatusResource;
use App\Http\Resources\Models\StaffResource;
use App\Models\Item;
use App\Models\Item\Scopes\ByTypes;
use App\Models\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        $activeItems        = $order->activeItemOrders();
        $totalItemsQuantity = (int) $activeItems->sum('quantity');
        $totalLineItems     = $activeItems->count();
        $items              = $activeItems->with('item', function(BelongsTo $relation) {
            $relation->scoped(new ByTypes([Item::TYPE_PART, Item::TYPE_SUPPLY]));
        })->take(3)->get()->pluck('item')->filter();

        $pointData     = (App::make(CalculatePoints::class, ['order' => $order]))->execute();
        $pubnubChannel = (App::make(GetChannelByOrder::class, ['order' => $order]))->execute();
        $baseResource  = $this->baseResource->toArray($request);
        $invoice       = $order->getFirstMedia(MediaCollectionNames::INVOICE);
        $staff         = $order->lastOrderStaff?->staff;

        return array_replace_recursive($baseResource, [
            'current_status'       => new OrderSubstatusResource($order->lastStatus),
            'total'                => $order->total,
            'supplier'             => new SupplierResource($order->supplier),
            'total_items_quantity' => $totalItemsQuantity,
            'total_line_items'     => $totalLineItems,
            'channel'              => $pubnubChannel,
            'items'                => ItemResource::collection($items),
            'bid_number'           => $order->bid_number,
            'points'               => $pointData->points(),
            'note'                 => $order->note,
            'invoice'              => $invoice ? new InvoiceResource($invoice) : null,
            'working_on_it'        => $staff ? new StaffResource($staff) : null,
        ]);
    }

    public static function jsonSchema(): array
    {
        return array_replace_recursive(OrderResource::jsonSchema(), [
            'properties' => [
                'current_status'       => OrderSubstatusResource::jsonSchema(),
                'total'                => ['type' => ['number', 'null']],
                'supplier'             => SupplierResource::jsonSchema(),
                'total_items_quantity' => ['type' => ['integer']],
                'total_line_items'     => ['type' => ['integer']],
                'channel'              => ['type' => ['string']],
                'items'                => ItemResource::jsonSchema(),
                'bid_number'           => ['type' => ['string', 'null']],
                'points'               => ['type' => ['number']],
                'note'                 => ['type' => ['string', 'null']],
                'invoice'              => InvoiceResource::jsonSchema(),
                'working_on_it'        => StaffResource::jsonSchema(),
            ],
            'required'   => [
                'current_status',
                'total',
                'supplier',
                'total_items_quantity',
                'total_line_items',
                'channel',
                'items',
                'bid_number',
                'points',
                'note',
                'invoice',
            ],
        ]);
    }
}
