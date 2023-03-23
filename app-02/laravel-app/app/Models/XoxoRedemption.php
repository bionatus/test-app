<?php

namespace App\Models;

use Database\Factories\XoxoRedemptionFactory;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @method static XoxoRedemptionFactory factory()
 *
 * @mixin XoxoRedemption
 */
class XoxoRedemption extends Model
{
    use HasUuid;

    /* |--- CONSTANTS ---| */
    const MORPH_ALIAS = 'xoxo_redemption';
    /* |--- GLOBAL VARIABLES ---| */
    protected $casts = [
        'uuid'               => 'string',
        'redemption_code'    => 'integer',
        'voucher_code'       => 'integer',
        'value_denomination' => 'integer',
        'amount_charged'     => 'integer',
    ];

    /* |--- RELATIONS ---| */
    public function point(): MorphOne
    {
        return $this->morphOne(Point::class, Point::POLYMORPHIC_NAME);
    }
    /* |--- FUNCTIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
