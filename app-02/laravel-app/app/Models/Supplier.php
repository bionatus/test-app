<?php

namespace App\Models;

use App\Constants\MediaCollectionNames;
use App\Constants\MediaConversionNames;
use App\Models\Scopes\ByType;
use App\Models\Scopes\ByZipCode;
use App\Traits\HasFlags;
use Config;
use Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @method static SupplierFactory factory()
 *
 * @mixin Supplier
 */
class Supplier extends Model implements HasMedia, HasSetting
{
    use HasFlags;
    use HasUuid;
    use HasState;
    use InteractsWithMedia;
    use Notifiable;

    /* |--- CONSTANTS ---| */
    const DEFAULT_PAYMENT_TERMS = '2.5%/10 Net 90';
    const DEFAULT_DAY           = 1;
    const DEFAULT_MONTH         = 1;
    const DEFAULT_TAKE_RATE     = 250;
    const DEFAULT_YEAR          = 2023;
    const MORPH_ALIAS           = 'supplier';
    const STEP                  = 0.01;
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'                   => 'integer',
        'uuid'                 => 'string',
        'verified_at'          => 'datetime',
        'welcome_displayed_at' => 'datetime',
        'take_rate_until'      => 'date',
        'published_at'         => 'datetime',
    ];

    /* |--- FUNCTIONS ---| */

    public function isOnCurriValidZipCode(): bool
    {
        return !($this->zip_code === null) && !ForbiddenZipCode::scoped(new ByZipCode($this->zip_code))->first();
    }

    public function isCurriDeliveryEnabled(): bool
    {
        return ($this->isOnCurriValidZipCode() && !$this->hasFlag(Flag::FORBIDDEN_CURRI));
    }

    public function verify(): self
    {
        $this->verified_at ??= Carbon::now();

        return $this;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollectionNames::IMAGES)->registerMediaConversions(function() {
            $this->addMediaConversion(MediaConversionNames::THUMB)->width(400)->height(400)->nonQueued();
        });

        $this->addMediaCollection(MediaCollectionNames::LOGO)->registerMediaConversions(function() {
            $this->addMediaConversion(MediaConversionNames::THUMB)->width(400)->height(400)->nonQueued();
        });
    }

    public function routeNotificationForTwilio()
    {
        return $this->contact_phone;
    }

    public function routeNotificationForTwilioByProkeepPhone()
    {
        return $this->prokeep_phone;
    }

    public function isInWorkingHours(): bool
    {
        $now           = Carbon::now();
        $day           = strtolower($now->format('l'));
        $supplierHours = $this->supplierHours->where('day', $day)->first();

        if ($supplierHours) {
            $startDate = Carbon::createFromFormat('g:i a', $supplierHours->from, $this->timezone);
            $endDate   = Carbon::createFromFormat('g:i a', $supplierHours->to, $this->timezone);

            return $now->between($startDate, $endDate);
        }

        return false;
    }

    public function hasContactEmail(): bool
    {
        return !!$this->contact_email;
    }

    /* |--- RELATIONS ---| */

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function user(string $user_id): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('user_id', 'status', 'customer_tier', 'cash_buyer', 'created_at')
            ->wherePivot('user_id', $user_id);
    }

    public function confirmedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('status', 'customer_tier', 'cash_buyer', 'created_at')
            ->wherePivot('status', SupplierUser::STATUS_CONFIRMED);
    }

    public function unconfirmedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('status', 'customer_tier', 'cash_buyer', 'created_at')
            ->wherePivot('status', SupplierUser::STATUS_UNCONFIRMED);
    }

    public function supplierUsers(): HasMany
    {
        return $this->hasMany(SupplierUser::class);
    }

    public function supplierHours(): HasMany
    {
        return $this->hasMany(SupplierHour::class);
    }

    public function supplierCompany(): BelongsTo
    {
        return $this->belongsTo(SupplierCompany::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function contact(): HasOne
    {
        return $this->hasOne(Staff::class)->scoped(new ByType(Staff::TYPE_CONTACT));
    }

    public function accountant(): HasOne
    {
        return $this->hasOne(Staff::class)->scoped(new ByType(Staff::TYPE_ACCOUNTANT));
    }

    public function manager(): HasOne
    {
        return $this->hasOne(Staff::class)->scoped(new ByType(Staff::TYPE_MANAGER));
    }

    public function counters(): HasMany
    {
        return $this->hasMany(Staff::class)->scoped(new ByType(Staff::TYPE_COUNTER));
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class);
    }

    public function brandSuppliers(): HasMany
    {
        return $this->hasMany(BrandSupplier::class);
    }

    public function supplierInvitations(): HasMany
    {
        return $this->hasMany(SupplierInvitation::class);
    }

    public function inviterUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, SupplierInvitation::class)->withTimestamps();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function pubnubChannels(): HasMany
    {
        return $this->hasMany(PubnubChannel::class);
    }

    public function settingSuppliers(): HasMany
    {
        return $this->hasMany(SettingSupplier::class);
    }

    public function customItems(): MorphMany
    {
        return $this->morphMany(CustomItem::class, CustomItem::POLYMORPHIC_NAME);
    }

    public function recommendedReplacements(): HasMany
    {
        return $this->hasMany(RecommendedReplacement::class);
    }

    public function apiUsages(): HasMany
    {
        return $this->hasMany(ApiUsage::class);
    }

    public function serviceLogs(): MorphMany
    {
        $connection = Config::get('database.default_stats');

        return $this->setConnection($connection)->morphMany(ServiceLog::class, ServiceLog::POLYMORPHIC_NAME);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orderSnaps(): HasMany
    {
        return $this->hasMany(OrderSnap::class);
    }

    /* |--- ACCESSORS ---| */

    public function getDistanceAttribute(): ?float
    {
        return !empty($this->attributes['distance']) ? round($this->attributes['distance'], 2) : null;
    }
    /* |--- MUTATORS ---| */
}
