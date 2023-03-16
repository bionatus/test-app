<?php

namespace App\Models;

use Database\Factories\AuthenticationCodeFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static AuthenticationCodeFactory factory()
 *
 * @mixin AuthenticationCode
 */
class AuthenticationCode extends Model
{
    const TYPE_VERIFICATION = 'verification';
    const TYPE_LOGIN        = 'login';
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'       => 'integer',
        'phone_id' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */

    public function isLogin(): bool
    {
        return self::TYPE_LOGIN === $this->type;
    }

    /* |--- RELATIONS ---| */

    public function phone(): BelongsTo
    {
        return $this->belongsTo(Phone::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
