<?php

namespace App\Models;

use App;
use App\Casts\Money;
use App\Constants\MediaCollectionNames;
use App\Constants\RouteParameters;
use App\Handlers\OrderSubstatus\OrderSubstatusCurriHandler;
use App\Handlers\OrderSubstatus\OrderSubstatusPickupHandler;
use App\Handlers\OrderSubstatus\OrderSubstatusShipmentHandler;
use App\Handlers\OrderSubstatus\OrderSubstatusUpdated;
use App\Models\ItemOrder\Scopes\ByInitialRequest;
use App\Models\ItemOrder\Scopes\IsPart;
use App\Models\ItemOrder\Scopes\IsSupplierCustomItem;
use App\Models\Scopes\ByStatuses;
use Database\Factories\OrderFactory;
use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @method static OrderFactory factory()
 *
 * @mixin Order
 */
class Order extends Model implements HasMedia
{
    use HasUuid, LogsActivity, InteractsWithMedia;

    /* |--- CONSTANTS ---| */
    const MORPH_ALIAS                  = 'order';
    const DEFAULT_DISCOUNT             = 0;
    const DEFAULT_TAX                  = 0;
    const TYPE_ORDER_LIST_AVAILABILITY = 'availability-requests';
    const TYPE_ORDER_LIST_APPROVED     = 'approved';
    /* |--- GLOBAL VARIABLES ---| */
    protected static array  $recordEvents  = ['updated'];
    protected static string $logName       = Activity::ORDER_LOG;
    protected static array  $logAttributes = ['*'];
    protected static bool   $logOnlyDirty  = true;
    protected               $with          = ['lastStatus'];
    protected               $casts         = [
        'id'         => 'integer',
        'uuid'       => 'string',
        'discount'   => Money::class,
        'tax'        => Money::class,
        'total'      => Money::class,
        'paid_total' => Money::class,
    ];

    /* |--- FUNCTIONS ---| */

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        if (RouteParameters::SUPPLIER_CUSTOM_ITEM_ITEM_ORDER === $childType) {
            return ItemOrder::scoped(new ByUuid($value))
                ->scoped(new IsSupplierCustomItem())
                ->where('order_id', $this->getKey())
                ->firstOrFail();
        }

        if (RouteParameters::PART_ITEM_ORDER === $childType) {
            return ItemOrder::scoped(new ByUuid($value))
                ->scoped(new IsPart())
                ->where('order_id', $this->getKey())
                ->firstOrFail();
        }

        return parent::resolveChildRouteBinding($childType, $value, $field);
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->description = Activity::RESOURCE_ORDER . '.' . $eventName;
        $activity->resource    = Activity::RESOURCE_ORDER;
        $activity->event       = $eventName;
    }

    public function isOwner(User $user): bool
    {
        return $this->user->getKey() === $user->getKey();
    }

    public function isProcessor(Staff $staff): bool
    {
        return $this->supplier->getKey() === $staff->supplier->getKey();
    }

    public function isAssigned(): bool
    {
        return !!$this->working_on_it;
    }

    public function isPending(): bool
    {
        return $this->lastStatus->isPending();
    }

    public function isPendingApproval(): bool
    {
        return $this->lastStatus->isPendingApproval();
    }

    public function isApproved(): bool
    {
        return $this->lastStatus->isApproved();
    }

    public function isCanceled(): bool
    {
        return $this->lastStatus->isCanceled();
    }

    public function isCompleted(): bool
    {
        return $this->lastStatus->isCompleted();
    }

    public function lastSubStatusIsAnyOf(array $substatusIds): bool
    {
        return in_array($this->lastStatus->substatus_id, $substatusIds);
    }

    public function hasAvailability(): bool
    {
        $orderDelivery = $this->orderDelivery;

        return !!($orderDelivery?->date && $orderDelivery?->start_time && $orderDelivery?->end_time);
    }

    public function deliveryFee(): int
    {
        return $this->orderDelivery ? $this->orderDelivery->fee : 0;
    }

    public function doesntHavePendingItems(): bool
    {
        $items = $this->itemOrders;

        return $items->every(function($item) {
            return !$item->isPending();
        });
    }

    public function hadTruckStock(): bool
    {
        return $this->itemOrders()->scoped(new ByInitialRequest(false))->exists();
    }

    public function subTotal(): float
    {
        $items = $this->itemOrders;

        if ($items->isEmpty()) {
            return 0;
        }

        return $items->sum(function($item) {
            if ($item['status'] === ItemOrder::STATUS_AVAILABLE) {
                return $item['price'] * $item['quantity'];
            }

            return 0;
        });
    }

    public function subTotalWithDelivery(): float
    {
        return $this->subTotal() + $this->deliveryFee();
    }

    public function subTotalWithDeliveryAndDiscount(): float
    {
        return $this->subTotal() + $this->deliveryFee() - $this->discount;
    }

    public function availabilityTranslation(): string
    {
        $orderDelivery = $this->orderDelivery;
        $date          = $orderDelivery->date;
        $startTime     = $orderDelivery->start_time;
        $endTime       = $orderDelivery->end_time;

        if (!$orderDelivery || !$date || !$startTime || !$endTime) {
            return '';
        }

        try {
            return $orderDelivery->date->format('m-d-Y') . ' ' . $orderDelivery->time_range;
        } catch (Exception $exception) {
            // Silently ignored
        }

        return '';
    }

    public function totalPointsEarned(): int
    {
        return $this->points()->sum('points_earned');
    }

    public function getStatusName(): string
    {
        return $this->lastStatus->getStatusName();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollectionNames::INVOICE)->singleFile();
    }

    public function createSubstatusCurriHandler(): OrderSubstatusUpdated
    {
        return App::make(OrderSubstatusCurriHandler::class, ['order' => $this]);
    }

    public function createSubstatusPickupHandler(): OrderSubstatusUpdated
    {
        return App::make(OrderSubstatusPickupHandler::class, ['order' => $this]);
    }

    public function createSubstatusShipmentHandler(): OrderSubstatusUpdated
    {
        return App::make(OrderSubstatusShipmentHandler::class, ['order' => $this]);
    }

    /* |--- RELATIONS ---| */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function oem(): BelongsTo
    {
        return $this->belongsTo(Oem::class);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class);
    }

    public function missedOrderRequests(): HasMany
    {
        return $this->hasMany(MissedOrderRequest::class);
    }

    public function itemOrders(): HasMany
    {
        return $this->hasMany(ItemOrder::class);
    }

    public function activeItemOrders(): HasMany
    {
        return $this->itemOrders()->scoped(new ByStatuses([
            ItemOrder::STATUS_AVAILABLE,
            ItemOrder::STATUS_PENDING,
        ]));
    }

    public function availableItemOrders(): HasMany
    {
        return $this->itemOrders()->scoped(new ByStatuses([
            ItemOrder::STATUS_AVAILABLE,
        ]));
    }

    public function availableAndRemovedItemOrders(): HasMany
    {
        return $this->itemOrders()->scoped(new ByStatuses([
            ItemOrder::STATUS_AVAILABLE,
            ItemOrder::STATUS_REMOVED,
        ]));
    }

    public function orderDelivery(): HasOne
    {
        return $this->hasOne(OrderDelivery::class);
    }

    public function orderLockedData(): HasOne
    {
        return $this->hasOne(OrderLockedData::class);
    }

    public function points(): MorphMany
    {
        return $this->morphMany(Point::class, Point::POLYMORPHIC_NAME);
    }

    public function orderInvoices(): HasMany
    {
        return $this->hasMany(OrderInvoice::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(OrderInvoice::class)->where('type', OrderInvoice::TYPE_INVOICE);
    }

    public function credit(): HasOne
    {
        return $this->hasOne(OrderInvoice::class)->where('type', OrderInvoice::TYPE_CREDIT);
    }

    public function orderSubstatuses(): HasMany
    {
        return $this->hasMany(OrderSubstatus::class);
    }

    public function substatuses(): BelongsToMany
    {
        return $this->belongsToMany(Substatus::class);
    }

    public function lastStatus(): HasOne
    {
        return $this->hasOne(OrderSubstatus::class)->latestOfMany();
    }

    public function sharedOrders(): HasMany
    {
        return $this->hasMany(SharedOrder::class);
    }

    public function pendingApprovalView(): HasOne
    {
        return $this->hasOne(PendingApprovalView::class);
    }

    public function cartOrder(): HasOne
    {
        return $this->hasOne(CartOrder::class);
    }

    public function orderSnaps(): HasMany
    {
        return $this->hasMany(OrderSnap::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function staffs(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class);
    }

    public function orderStaffs(): HasMany
    {
        return $this->hasMany(OrderStaff::class);
    }

    public function lastOrderStaff(): HasOne
    {
        return $this->hasOne(OrderStaff::class)->latestOfMany();
    }


    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
