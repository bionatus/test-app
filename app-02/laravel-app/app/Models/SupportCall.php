<?php

namespace App\Models;

use Database\Factories\SupportCallFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static SupportCallFactory factory()
 *
 * @mixin SupportCall
 */
class SupportCall extends Model
{
    use HasUuid;

    /* |--- CONSTANTS ---| */
    const CATEGORY_OEM           = 'oem';
    const CATEGORY_MISSING_OEM   = 'missing-oem';
    const MINIMUM_POINTS_TO_CALL = 1000;
    /* |--- GLOBAL VARIABLES ---| */
    protected $casts = [
        'id'      => 'integer',
        'uuid'    => 'string',
        'user_id' => 'integer',
        'oem_id'  => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function missingOemBrand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function oem(): BelongsTo
    {
        return $this->belongsTo(Oem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
