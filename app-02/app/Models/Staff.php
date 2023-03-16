<?php

namespace App\Models;

use App\Notifications\LiveApi\V1\ResetPasswordNotification;
use Database\Factories\StaffFactory;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @method static StaffFactory factory()
 *
 * @mixin Staff
 */
class Staff extends Authenticatable implements JWTSubject, CanResetPasswordContract, PerformsOemSearches, PerformsPartSearches, HasSetting
{
    use HasUuid, CanResetPassword, Notifiable;

    const MORPH_ALIAS     = 'staff';
    const TYPE_ACCOUNTANT = 'accountant';
    const TYPE_CONTACT    = 'contact';
    const TYPE_COUNTER    = 'counter';
    const TYPE_MANAGER    = 'manager';
    const TYPE_OWNER      = 'owner';
    const STAFF_TYPES = [
        self::TYPE_ACCOUNTANT,
        self::TYPE_CONTACT,
        self::TYPE_COUNTER,
        self::TYPE_MANAGER,
        self::TYPE_OWNER,
    ];
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'                      => 'integer',
        'supplier_id'             => 'integer',
        'uuid'                    => 'string',
        'initial_password_set_at' => 'date',
        'tos_accepted_at'         => 'date',
    ];

    /* |--- FUNCTIONS ---| */

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function hasSetInitialPassword(): bool
    {
        return !!$this->initial_password_set_at;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function routeNotificationForTwilio()
    {
        return $this->phone;
    }

    public function isCounter(): bool
    {
        return $this->type === self::TYPE_COUNTER;
    }

    /* |--- RELATIONS ---| */

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function partDetailCounters(): HasMany
    {
        return $this->hasMany(PartDetailCounter::class);
    }

    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(Part::class, PartDetailCounter::class)->withTimestamps();
    }

    public function oemDetailCounters(): HasMany
    {
        return $this->hasMany(OemDetailCounter::class);
    }

    public function oems(): BelongsToMany
    {
        return $this->belongsToMany(Oem::class, OemDetailCounter::class)->withTimestamps();
    }

    public function brandDetailCounters(): HasMany
    {
        return $this->hasMany(BrandDetailCounter::class);
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, BrandDetailCounter::class)->withTimestamps();
    }

    public function partSearches(): HasMany
    {
        return $this->hasMany(PartSearchCounter::class);
    }

    public function oemSearches(): HasMany
    {
        return $this->hasMany(OemSearchCounter::class);
    }

    public function settingStaffs(): HasMany
    {
        return $this->hasMany(SettingStaff::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class);
    }

    public function orderStaffs(): HasMany
    {
        return $this->hasMany(OrderStaff::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
