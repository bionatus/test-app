<?php

namespace App\Models;

use Database\Factories\CartItemFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @method static CartItemFactory factory()
 *
 * @mixin CartItem
 */
class CartItem extends Pivot
{
    use HasUuid, LogsActivity;

    /* |--- CONSTANTS ---| */
    const MORPH_ALIAS = 'cart_item';
    /* |--- GLOBAL VARIABLES ---| */
    protected static array  $recordEvents  = ['deleted'];
    protected static string $logName       = Activity::CART_ITEM_LOG;
    protected static array  $logAttributes = ['*'];
    protected static bool   $logOnlyDirty  = true;
    protected               $casts         = [
        'item_id' => 'integer',
        'cart_id' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */
    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->description = Activity::RESOURCE_CART_ITEM . '.' . $eventName;
        $activity->resource    = Activity::RESOURCE_CART_ITEM;
        $activity->event       = $eventName;
    }

    /* |--- RELATIONS ---| */

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
