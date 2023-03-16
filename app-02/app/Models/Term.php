<?php

namespace App\Models;

use Database\Factories\TermFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static TermFactory factory()
 *
 * @mixin Term
 */
class Term extends Model
{
    /* |--- GLOBAL VARIABLES ---| */
    protected $casts = [
        'id'          => 'integer',
        'required_at' => 'date',
    ];
    /* |--- CONSTANTS ---| */
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function termUsers(): HasMany
    {
        return $this->hasMany(TermUser::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
