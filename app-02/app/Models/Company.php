<?php

namespace App\Models;

use App\Types\Coordinates;
use App\Types\CountryDataType;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Str;

/**
 * @method static CompanyFactory factory()
 *
 * @mixin Company
 */
class Company extends Model
{
    use HasUuid;
    use HasState;

    /* |--- CONSTANTS ---| */
    const MORPH_ALIAS = 'company';
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'uuid'      => 'string',
        'latitude'  => 'string',
        'longitude' => 'string',
    ];

    /* |--- FUNCTIONS ---| */

    public function hasValidCoordinates(): bool
    {
        return Coordinates::isValidLatitude($this->latitude) && Coordinates::isValidLongitude($this->longitude);
    }

    public function hasValidZipCode(): bool
    {
        if (CountryDataType::UNITED_STATES !== $this->country) {
            return false;
        }

        return 5 === Str::length($this->zip_code);
    }

    /* |--- RELATIONS ---| */

    public function companyUsers(): HasMany
    {
        return $this->hasMany(CompanyUser::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
