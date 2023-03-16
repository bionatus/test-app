<?php

namespace App\Models;

use Database\Factories\XoxoVoucherFactory;
use Illuminate\Support\Collection;
use Str;

/**
 * @method static XoxoVoucherFactory factory()
 *
 * @mixin XoxoVoucher
 */
class XoxoVoucher extends Model
{
    /* |--- CONSTANTS ---| */
    const TYPE_OPEN_VALUE = 'open_value';
    /* |--- GLOBAL VARIABLES ---| */
    protected $casts = [
        'code' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */

    public function getRouteKeyName(): string
    {
        return 'code';
    }
    /* |--- RELATIONS ---| */
    /* |--- FUNCTIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
    /* |--- ACCESSORS ---| */
    public function getFirstDenominationAttribute(): int
    {
        return (int) Str::of($this->value_denominations)->explode(',')->first();
    }

    public function getDenominationsAttribute(): Collection
    {
        return Str::of($this->value_denominations)->explode(',');
    }
    /* |--- MUTATORS ---| */
}
