<?php

namespace App\Models;

use App\Casts\Money;
use Database\Factories\OrderSnapFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static OrderSnapFactory factory()
 *
 * @mixin OrderSnap
 */
class OrderSnap extends Model
{
    /* |--- CONSTANTS ---| */
    /* |--- GLOBAL VARIABLES ---| */
    protected $casts = [
        'id'       => 'integer',
        'discount' => Money::class,
        'tax'      => Money::class,
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class);
    }

    public function itemOrderSnaps(): HasMany
    {
        return $this->hasMany(ItemOrderSnap::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

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

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */

}
