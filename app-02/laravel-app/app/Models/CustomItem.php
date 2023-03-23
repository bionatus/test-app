<?php

namespace App\Models;

use Database\Factories\CustomItemFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @method static CustomItemFactory factory()
 *
 * @mixin CustomItem
 */
class CustomItem extends Model implements IsOrderable
{
    const MORPH_ALIAS      = 'custom_item';
    const POLYMORPHIC_NAME = 'creator';
    /* |--- GLOBAL VARIABLES ---| */
    public    $incrementing = false;
    public    $timestamps   = false;
    protected $casts        = [
        'id'         => 'integer',
        'creator_id' => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function item(): HasOne
    {
        return $this->hasOne(Item::class, 'id');
    }

    public function creator(): MorphTo
    {
        return $this->morphTo();
    }

    /* |--- ACCESSORS ---| */
    public function getReadableTypeAttribute(): string
    {
        return $this->name;
    }
    /* |--- MUTATORS ---| */
}
