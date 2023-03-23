<?php

namespace App\Models;

use Database\Factories\ReplacementFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Str;

/**
 * @method static ReplacementFactory factory()
 *
 * @mixin Replacement
 */
class Replacement extends Model
{
    use HasUuid;

    const TYPE_SINGLE  = 'single';
    const TYPE_GROUPED = 'grouped';
    protected $casts = [
        'original_part_id' => 'integer',
    ];
    /* |--- GLOBAL VARIABLES ---| */
    /* |--- FUNCTIONS ---| */

    public function isSingle(): bool
    {
        return $this->type === self::TYPE_SINGLE;
    }

    public function completeNotes(): ?string
    {
        $completeNote    = null;
        $replacementNote = $this->note;

        if ($replacementNote) {
            $completeNote .= Str::substr($replacementNote->value, 3);
        }
        if ($this->isSingle() && $this->singleReplacement->part->note) {
            $completeNote .= ($replacementNote) ? PHP_EOL : '';
            $completeNote .= $this->singleReplacement->part->note->value;
        }

        return $completeNote;
    }

    /* |--- RELATIONS ---| */

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'original_part_id');
    }

    public function singleReplacement(): HasOne
    {
        return $this->hasOne(SingleReplacement::class, 'id');
    }

    public function groupedReplacements(): HasMany
    {
        return $this->hasMany(GroupedReplacement::class);
    }

    public function note(): hasOne
    {
        return $this->hasOne(ReplacementNote::class, 'replacement_id');
    }

    public function itemOrders(): HasMany
    {
        return $this->hasMany(ItemOrder::class);
    }

    public function itemOrderSnaps(): HasMany
    {
        return $this->hasMany(ItemOrderSnap::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
