<?php

namespace App\Models;

use App\Casts\Money;
use Database\Factories\OrderDeliveryFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Str;

/**
 * @method static OrderDeliveryFactory factory()
 *
 * @mixin OrderDelivery
 */
class OrderDelivery extends Model
{
    use LogsActivity;

    /* |--- CONSTANTS ---| */
    const MORPH_ALIAS             = 'order_delivery';
    const TYPE_PICKUP             = 'pickup';
    const TYPE_CURRI_DELIVERY     = 'curri_delivery';
    const TYPE_OTHER_DELIVERY     = 'other_delivery';
    const TYPE_SHIPMENT_DELIVERY  = 'shipment_delivery';
    const TYPE_WAREHOUSE_DELIVERY = 'warehouse_delivery';
    const TYPE_LEGACY_ALL         = [
        self::TYPE_PICKUP,
        self::TYPE_CURRI_DELIVERY,
        self::TYPE_OTHER_DELIVERY,
        self::TYPE_SHIPMENT_DELIVERY,
        self::TYPE_WAREHOUSE_DELIVERY,
    ];
    const TYPE_ALL                = [
        self::TYPE_PICKUP,
        self::TYPE_CURRI_DELIVERY,
        self::TYPE_SHIPMENT_DELIVERY,
    ];
    /* |--- GLOBAL VARIABLES ---| */
    protected static array  $recordEvents  = ['updated'];
    protected static string $logName       = Activity::ORDER_LOG;
    protected static array  $logAttributes = ['*'];
    protected static bool   $logOnlyDirty  = true;
    protected               $casts         = [
        'id'                   => 'integer',
        'fee'                  => Money::class,
        'requested_date'       => 'datetime:Y-m-d',
        'date'                 => 'datetime:Y-m-d',
        'requested_start_time' => 'datetime:H:i',
        'requested_end_time'   => 'datetime:H:i',
        'start_time'           => 'datetime:H:i',
        'end_time'             => 'datetime:H:i',
    ];

    /* |--- FUNCTIONS ---| */

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->description = Activity::RESOURCE_ORDER_DELIVERY . '.' . $eventName;
        $activity->resource    = Activity::RESOURCE_ORDER_DELIVERY;
        $activity->event       = $eventName;
    }

    public function isDelivery(): bool
    {
        return in_array($this->type, [
            self::TYPE_CURRI_DELIVERY,
            self::TYPE_OTHER_DELIVERY,
            self::TYPE_SHIPMENT_DELIVERY,
            self::TYPE_WAREHOUSE_DELIVERY,
        ]);
    }

    public function isPickup(): bool
    {
        return $this->type === self::TYPE_PICKUP;
    }

    public function isCurriDelivery(): bool
    {
        return $this->type === self::TYPE_CURRI_DELIVERY;
    }

    public function isOtherDelivery(): bool
    {
        return $this->type === self::TYPE_OTHER_DELIVERY;
    }

    public function isShipmentDelivery(): bool
    {
        return $this->type === self::TYPE_SHIPMENT_DELIVERY;
    }

    public function isWarehouseDelivery(): bool
    {
        return $this->type === self::TYPE_WAREHOUSE_DELIVERY;
    }

    public function isAValidDateTimeForSupplier(): bool
    {

        $hours         = $this->end_time->format('gA');
        $fullDate      = $this->date->format('Y-m-d') . ' ' . $hours;
        $timezone      = $this->order->supplier->timezone;
        $formattedDate = Carbon::createFromFormat('Y-m-d gA', $fullDate, $timezone)->startOfHour();

        return Carbon::now()->lt($formattedDate);
    }

    public function startTime(): \Carbon\Carbon
    {
        $date = $this->date->format('Y-m-d');
        $time = $this->start_time->format('H:i');

        return Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
    }

    public function isNeededNow(): bool
    {
        return !!$this->is_needed_now;
    }

    public function isNeededLater(): bool
    {
        return !$this->is_needed_now;
    }

    /* |--- RELATIONS ---| */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function deliverable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'type', 'id');
    }

    /* |--- ACCESSORS ---| */
    public function getReadableTypeAttribute(): string
    {
        return Str::of($this->type)->replace('_', ' ')->title();
    }

    public function getTimeRangeAttribute(): string
    {
        return Carbon::create($this->start_time)->format('gA') . ' - ' . Carbon::create($this->end_time)->format('gA');
    }
    /* |--- MUTATORS ---| */
}
