<?php

namespace App\Models;

use App\Casts\Money;
use Database\Factories\ItemOrderFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @method static ItemOrderFactory factory()
 *
 * @mixin ItemOrder
 */
class ItemOrder extends Pivot
{
    use HasUuid, LogsActivity;

    /* |--- CONSTANTS ---| */
    const MORPH_ALIAS                  = 'item_order';
    const REPLACEMENT_TYPE_GENERIC     = 'generic';
    const REPLACEMENT_TYPE_REPLACEMENT = 'replacement';
    const STATUS_AVAILABLE             = 'available';
    const STATUS_NOT_AVAILABLE         = 'not_available';
    const STATUS_PENDING               = 'pending';
    const STATUS_REMOVED               = 'removed';
    const STATUS_SEE_BELOW_ITEM        = 'see_below_item';
    const VALID_STATUSES               = [
        self::STATUS_AVAILABLE,
        self::STATUS_NOT_AVAILABLE,
        self::STATUS_PENDING,
        self::STATUS_REMOVED,
    ];
    /* |--- GLOBAL VARIABLES ---| */
    protected static array  $recordEvents  = ['updated'];
    protected static string $logName       = Activity::ORDER_LOG;
    protected static array  $logAttributes = ['*'];
    protected static bool   $logOnlyDirty  = true;
    protected               $casts         = [
        'id'                 => 'integer',
        'uuid'               => 'string',
        'item_id'            => 'integer',
        'order_id'           => 'integer',
        'quantity'           => 'integer',
        'quantity_requested' => 'integer',
        'price'              => Money::class,
    ];
    protected               $touches       = ['order'];

    /* |--- FUNCTIONS ---| */

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->description = Activity::RESOURCE_ORDER_ITEM . '.' . $eventName;
        $activity->resource    = Activity::RESOURCE_ORDER_ITEM;
        $activity->event       = $eventName;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function hasAnyReplacement(): bool
    {
        return $this->replacement || $this->generic_part_description;
    }

    public function isRemoved(): bool
    {
        return $this->status === self::STATUS_REMOVED;
    }

    /* |--- RELATIONS ---| */

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function replacement(): BelongsTo
    {
        return $this->belongsTo(Replacement::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
