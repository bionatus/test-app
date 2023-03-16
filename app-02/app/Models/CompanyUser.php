<?php

namespace App\Models;

use Database\Factories\CompanyUserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static CompanyUserFactory factory()
 *
 * @mixin CompanyUser
 */
class CompanyUser extends Pivot
{
    /* |--- GLOBAL VARIABLES ---| */
    protected $table = 'company_user';
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
