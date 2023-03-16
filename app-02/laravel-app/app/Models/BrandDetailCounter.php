<?php

namespace App\Models;

use Database\Factories\BrandDetailCounterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static BrandDetailCounterFactory factory()
 *
 * @mixin BrandDetailCounter
 */
class BrandDetailCounter extends Model
{
    use HasFactory;

    /* |--- GLOBAL VARIABLES ---| */
    public    $table = 'brand_detail_counter';
    protected $casts = [
        'id'       => 'integer',
        'brand_id' => 'integer',
        'user_id'  => 'integer',
        'staff_id' => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
