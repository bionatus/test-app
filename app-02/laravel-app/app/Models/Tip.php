<?php

namespace App\Models;

use Database\Factories\TipFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static TipFactory factory()
 *
 * @mixin Tip
 */
class Tip extends Model
{
    /* |--- GLOBAL VARIABLES ---| */
    public    $timestamps = false;
    protected $casts      = [
        'id' => 'integer',
    ];

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }
}
