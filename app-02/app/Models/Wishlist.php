<?php

namespace App\Models;

use Database\Factories\WishListFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static WishlistFactory factory()
 *
 * @mixin Wishlist
 */
class Wishlist extends Model
{
    use HasUuid;

    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'user_id' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */
    public function isOwner(User $user): bool
    {
        return $this->user->getKey() === $user->getKey();
    }

    /* |--- RELATIONS ---| */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class);
    }

    public function itemWishlists(): HasMany
    {
        return $this->hasMany(ItemWishlist::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
