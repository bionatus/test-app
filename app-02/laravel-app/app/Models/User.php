<?php

namespace App\Models;

use App;
use App\Actions\Models\SettingUser\GetNotificationSetting;
use App\Actions\Models\Term\GetCurrentTerm;
use App\AppNotification;
use App\Constants\MediaCollectionNames;
use App\Constants\MediaConversionNames;
use App\Models\AppNotification\Scopes\Unread as UnreadAppNotification;
use App\Models\InternalNotification\Scopes\Unread as UnreadInternalNotification;
use App\Models\Level\Scopes\ByLevelRange;
use App\Models\Order\Scopes\ByLastSubstatuses;
use App\Models\Scopes\ByRouteKey;
use App\Models\Scopes\ByUser;
use App\Models\SupplierUser\Scopes\ByVisibleByUser;
use App\Models\TermUser\Scopes\ByTerm;
use App\Models\UserTaggable\Scopes\ByTaggable;
use App\Notifications\CreatePasswordNotification;
use App\Traits\HasFlags;
use Config;
use Database\Factories\UserFactory;
use Illuminate\Auth\Passwords;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Passport\HasApiTokens;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Storage;
use Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @method static UserFactory factory()
 *
 * @mixin User
 */
class User extends Authenticatable implements JWTSubject, HasMedia, CanResetPassword, PerformsOemSearches, PerformsPartSearches, PerformsSupplySearches, HasSetting
{
    use CausesActivity;
    use HasApiTokens;
    use Notifiable;
    use HasRoles;
    use HasState;
    use InteractsWithMedia;
    use Passwords\CanResetPassword;
    use HasSlug {
        generateNonUniqueSlug as traitGenerateNonUniqueSlug;
    }
    use HasFlags;

    const MORPH_ALIAS = 'user';
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'                        => 'integer',
        'trial_ends_at'             => 'datetime',
        'uses_two_factor_auth'      => 'boolean',
        'accreditated'              => 'boolean',
        'accreditated_at'           => 'date',
        'apps'                      => 'array',
        'call_date'                 => 'datetime',
        'registration_completed'    => 'boolean',
        'registration_completed_at' => 'datetime',
        'terms'                     => 'boolean',
        'verified_at'               => 'datetime',
        'manual_download_count'     => 'integer',
        'hat_requested'             => 'boolean',
        'created_at'                => 'datetime',
        'updated_at'                => 'datetime',
        'disabled_at'               => 'datetime',
        'orders_count'              => 'integer',
    ];

    /* |--- FUNCTIONS ---| */

    public function isAgent(): bool
    {
        return !!$this->agent;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function routeNotificationForFcm()
    {
        $pushNotificationTokens      = $this->pushNotificationTokens()->with('device')->get();
        $validPushNotificationTokens = $pushNotificationTokens->filter(function(
            PushNotificationToken $pushNotificationToken
        ) {
            return version_compare(Config::get('notifications.push.min_app_version'),
                $pushNotificationToken->device->app_version, '<=');
        });

        return $validPushNotificationTokens->pluck('token')->toArray();
    }

    public function getPhone(): ?string
    {
        /** @var Phone $phone */
        $phone = $this->phone()->first();

        return $phone ? $phone->fullNumber() : null;
    }

    public function routeNotificationForTwilio()
    {
        return $this->getPhone();
    }

    public function isFollowing(IsTaggable $taggable): bool
    {
        $following = $this->followedTags()->scoped(new ByTaggable($taggable))->count();

        return !!$following;
    }

    public function isModerator(): bool
    {
        return in_array($this->email, [
            'acurry@bionatusllc.com',
            'dbunnett@bluon.com',
            'pcapuciati@bluon.com',
        ]);
    }

    public function allSettingUsers(): Collection
    {
        $settingsQuery = Setting::with([
            'settingUsers' => function(HasMany $builder) {
                $builder->scoped(new ByUser($this));
            },
        ]);

        if (!$this->isAgent()) {
            $settingsQuery->where('group', '<>', Setting::GROUP_AGENT);
        }

        $settings = $settingsQuery->get();

        return $settings->map(function(Setting $setting) {
            return $setting->settingUsers->first() ?? $this->settingUsers()->make([
                'setting_id' => $setting->id,
                'value'      => $setting->value,
            ]);
        });
    }

    public function getUnreadNotificationsCount(): int
    {
        $internalNotificationsCount = $this->internalNotifications()->scoped(new UnreadInternalNotification())->count();
        $appNotificationsCount      = $this->appNotifications()->scoped(new UnreadAppNotification())->count();

        return $internalNotificationsCount + $appNotificationsCount;
    }

    /**
     * @return SettingUser|null|\Illuminate\Database\Eloquent\Model
     */
    public function setSetting(Setting $setting, $value): ?SettingUser
    {
        if (!$setting->exists) {
            return null;
        }

        return $setting->settingUsers()
            ->scoped(new ByUser($this))
            ->updateOrCreate(['user_id' => $this->getKey()], ['value' => $value]);
    }

    public function fullName(): string
    {
        if (!empty($this->first_name) || !empty($this->last_name)) {
            return trim($this->first_name . ' ' . $this->last_name);
        }

        return trim($this->name);
    }

    public function photoUrl(): ?string
    {
        return !empty($this->photo) ? asset(Storage::url($this->photo)) : null;
    }

    public function isVerified(): bool
    {
        return !!$this->verified_at;
    }

    public function isDisabled(): bool
    {
        return !!$this->disabled_at;
    }

    public function isAccredited(): bool
    {
        return !!$this->accreditated;
    }

    public function isRegistered(): bool
    {
        return !!$this->registration_completed;
    }

    public function isSupportCallDisabled(): bool
    {
        return $this->hasFlag(Flag::SUPPORT_CALL_DISABLED);
    }

    public function hasToSAccepted(): bool
    {
        $currentTerm = App::make(GetCurrentTerm::class)->execute();

        return $currentTerm && $this->termUsers()->scoped(new ByTerm($currentTerm))->exists();
    }

    public function totalPointsEarned(): int
    {
        return $this->points()->sum('points_earned');
    }

    public function totalPointsRedeemed(): int
    {
        return $this->points()->sum('points_redeemed');
    }

    public function availablePoints(): int
    {
        $userAvailablePoints = $this->points()
            ->selectRaw('sum(points_earned) as total_earned, sum(points_redeemed) as total_redeemed')
            ->first();

        return $userAvailablePoints->total_earned - $userAvailablePoints->total_redeemed;
    }

    public function availablePointsToCash(): float
    {
        return round($this->availablePoints() * Point::CASH_VALUE, 2);
    }

    public function currentLevel(): Level
    {
        /** @var LevelUser $currentLevel */
        $currentLevel = $this->levelUsers()->latest(LevelUser::keyName())->first();

        if (!$currentLevel) {
            /** @var Level $level */
            $level = Level::scoped(new ByRouteKey(Level::SLUG_LEVEL_0))->first();
            $this->levels()->attach($level);

            return $level;
        }

        return $currentLevel->level;
    }

    public function processLevel(): void
    {
        $totalPointsEarned   = $this->totalPointsEarned();
        $currentLevel        = $this->currentLevel();
        $shouldDecreaseLevel = !$currentLevel->isLowestLevel() && $totalPointsEarned < $currentLevel->from;
        $shouldIncreaseLevel = !$currentLevel->isHighestLevel() && $totalPointsEarned >= $currentLevel->to;

        if ($shouldDecreaseLevel || $shouldIncreaseLevel) {
            $newLevel = Level::scoped(new ByLevelRange($totalPointsEarned))->first();
            $this->levels()->attach($newLevel);
        }
        if ($shouldIncreaseLevel) {
            $this->unflag(Flag::SUPPORT_CALL_DISABLED);
        }
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['first_name', 'last_name'])
            ->saveSlugsTo('public_name')
            ->doNotGenerateSlugsOnCreate()
            ->doNotGenerateSlugsOnUpdate()
            ->usingSeparator('');
    }

    /** @noinspection DuplicatedCode */
    protected function generateNonUniqueSlug(): string
    {
        $slugField = $this->slugOptions->slugField;
        if ($this->hasCustomSlugBeenUsed() && !empty($this->getAttribute($slugField))) {
            return $this->getAttribute($slugField);
        }

        $separator = $this->slugOptions->slugSeparator;

        $this->slugOptions->slugSeparator = '-';

        $generatedSlug = $this->traitGenerateNonUniqueSlug();

        $slugSourceString = $this->getSlugSourceString();
        if ($this->slugOptions->slugSeparator === $slugSourceString || is_numeric(substr($slugSourceString, 0, 1))) {
            return 'User';
        }

        $explodedSlug = Collection::make(explode($this->slugOptions->slugSeparator, $generatedSlug));

        $this->slugOptions->slugSeparator = $separator;

        return $explodedSlug->map(fn(string $segment) => Str::ucfirst($segment))->join($separator);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollectionNames::IMAGES)->singleFile();
    }

    /**
     * @throws InvalidManipulation
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion(MediaConversionNames::THUMB)->width(400)->height(400);
    }

    public function shouldBeVerified(): bool
    {
        return $this->hasUserFieldsForVerificationFilled() && $this->hasCompany() && $this->hasSuppliers();
    }

    public function hasUserFieldsForVerificationFilled(): bool
    {
        $fields = Collection::make(['first_name', 'last_name', 'zip', 'address', 'country', 'state', 'city']);

        return !$fields->filter(fn($attribute) => null === $this->getAttribute($attribute))->count();
    }

    public function hasCompany(): bool
    {
        return !!$this->companyUser()->first();
    }

    public function hasSuppliers(): bool
    {
        return !!$this->visibleSuppliers()->count();
    }

    public function hasSupportCalls(): bool
    {
        return $this->supportCalls()->exists();
    }

    public function verify(): self
    {
        if ($this->shouldBeVerified()) {
            $this->verified_at = $this->verified_at ?? Carbon::now();

            return $this;
        }

        $this->verified_at = null;

        return $this;
    }

    public function requiresHubspotSync(): bool
    {
        $hubspotFields = [
            'first_name',
            'last_name',
            'email_name',
            'public_name',
            'hvac_supplier',
            'occupation',
            'apps',
            'service_manager_name',
            'service_manager_phone',
            'phone',
            'address',
            'address_2',
            'city',
            'state',
            'zip',
            'accreditated',
            'group_code',
            'calls_count',
            'manuals_count',
            'bio',
            'union',
            'experience_years',
            'company',
            'hat_requested',
            'verified_at',
            'photo',
        ];
        foreach ($this->getDirty() as $field => $value) {
            if (in_array($field, $hubspotFields)) {
                return true;
            }
        }

        return false;
    }

    public function sendCreatePasswordNotification($token)
    {
        $this->notify(new CreatePasswordNotification($token));
    }

    public function companyName(): ?string
    {
        return $this->companyUser ? $this->companyUser->company->name : null;
    }

    public function shouldSendPushNotificationWithoutSetting(): bool
    {
        if ($this->disabled_at) {
            return false;
        }

        if (!Config::get('notifications.push.enabled')) {
            return false;
        }

        return true;
    }

    public function shouldSendInAppNotification(string $slug): bool
    {
        return $this->shouldSendPushNotificationWithoutSetting() && App::make(GetNotificationSetting::class,
                ['user' => $this, 'slug' => $slug])->execute();
    }

    public function shouldSendSmsNotificationWithoutSetting(): bool
    {
        if (!Config::get('notifications.sms.enabled')) {
            return false;
        }

        if (!$this->phone()->first()) {
            return false;
        }

        return true;
    }

    public function shouldSendSmsNotification(string $slug): bool
    {
        return $this->shouldSendSmsNotificationWithoutSetting() && App::make(GetNotificationSetting::class,
                ['user' => $this, 'slug' => $slug])->execute();
    }

    public function shouldSendForumNotifications($slug): bool
    {
        if ($this->disabled_at) {
            return false;
        }

        $forumNotificationSlug = Setting::SLUG_DISABLE_FORUM_NOTIFICATION;

        if (App::make(GetNotificationSetting::class, ['user' => $this, 'slug' => $forumNotificationSlug])->execute()) {
            return false;
        }

        return App::make(GetNotificationSetting::class, ['user' => $this, 'slug' => $slug])->execute();
    }

    /* |--- RELATIONS ---| */

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function agent(): HasOne
    {
        return $this->hasOne(Agent::class, 'id');
    }

    public function pushNotificationTokens(): HasManyThrough
    {
        return $this->hasManyThrough(PushNotificationToken::class, Device::class);
    }

    public function followedTags(): HasMany
    {
        return $this->hasMany(UserTaggable::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function commentVotes(): HasMany
    {
        return $this->hasMany(CommentVote::class);
    }

    public function relatedActivity(): HasMany
    {
        return $this->hasMany(RelatedActivity::class);
    }

    public function settings(): BelongsToMany
    {
        return $this->belongsToMany(Setting::class);
    }

    public function settingUsers(): HasMany
    {
        return $this->hasMany(SettingUser::class);
    }

    public function internalNotifications(): HasMany
    {
        return $this->hasMany(InternalNotification::class);
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function phone(): HasOne
    {
        return $this->hasOne(Phone::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class)->using(SupplierUser::class)->withPivot([
            'visible_by_user',
            'user_id',
        ])->withTimestamps();
    }

    public function visibleSuppliers(): BelongsToMany
    {
        return $this->suppliers()->scoped(new ByVisibleByUser(true));
    }

    public function supplierUsers(): HasMany
    {
        return $this->hasMany(SupplierUser::class);
    }

    public function visibleSupplierUsers(): HasMany
    {
        return $this->supplierUsers()->scoped(new ByVisibleByUser(true));
    }

    public function seriesUsers(): HasMany
    {
        return $this->hasMany(SeriesUser::class);
    }

    public function favoriteSeries(): BelongsToMany
    {
        return $this->belongsToMany(Series::class);
    }

    public function companyUser(): HasOne
    {
        return $this->hasOne(CompanyUser::class);
    }

    public function company(): HasOneThrough
    {
        return $this->hasOneThrough(Company::class, CompanyUser::class, 'user_id', 'id', 'id', 'company_id');
    }

    public function supplierInvitations(): HasMany
    {
        return $this->hasMany(SupplierInvitation::class);
    }

    public function invitedSuppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, SupplierInvitation::class)->withTimestamps();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function pubnubChannels(): HasMany
    {
        return $this->hasMany(PubnubChannel::class);
    }

    public function partDetailCounters(): HasMany
    {
        return $this->hasMany(PartDetailCounter::class);
    }

    public function partSearches(): HasMany
    {
        return $this->hasMany(PartSearchCounter::class);
    }

    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(Part::class, PartDetailCounter::class)->withTimestamps();
    }

    public function oemDetailCounters(): HasMany
    {
        return $this->hasMany(OemDetailCounter::class);
    }

    public function oemSearches(): HasMany
    {
        return $this->hasMany(OemSearchCounter::class);
    }

    public function oems(): BelongsToMany
    {
        return $this->belongsToMany(Oem::class, OemDetailCounter::class)->withTimestamps();
    }

    public function oemUsers(): HasMany
    {
        return $this->hasMany(OemUser::class);
    }

    public function terms(): BelongsToMany
    {
        return $this->belongsToMany(Term::class)->withTimestamps();
    }

    public function termUsers(): HasMany
    {
        return $this->hasMany(TermUser::class);
    }

    public function favoriteOems(): BelongsToMany
    {
        return $this->belongsToMany(Oem::class, OemUser::class)->withTimestamps();
    }

    public function points(): HasMany
    {
        return $this->hasMany(Point::class);
    }

    public function levelUsers(): HasMany
    {
        return $this->hasMany(LevelUser::class);
    }

    public function levels(): BelongsToMany
    {
        return $this->belongsToMany(Level::class)->withTimestamps();
    }

    public function postVotes(): HasMany
    {
        return $this->hasMany(PostVote::class);
    }

    public function sharedOrders(): HasMany
    {
        return $this->hasMany(SharedOrder::class);
    }

    public function customItems(): MorphMany
    {
        return $this->morphMany(CustomItem::class, CustomItem::POLYMORPHIC_NAME);
    }

    public function pendingApprovalViews(): HasMany
    {
        return $this->hasMany(PendingApprovalView::class);
    }

    public function supportCalls(): HasMany
    {
        return $this->hasMany(SupportCall::class);
    }

    public function commentUsers(): HasMany
    {
        return $this->hasMany(CommentUser::class);
    }

    public function taggedInComments(): BelongsToMany
    {
        return $this->belongsToMany(Comment::class);
    }

    public function supplierListViews(): HasMany
    {
        return $this->hasMany(SupplierListView::class);
    }

    public function supplySearches(): HasMany
    {
        return $this->hasMany(SupplySearchCounter::class);
    }

    public function supplyCategoryViews(): HasMany
    {
        return $this->hasMany(SupplyCategoryView::class);
    }

    public function cartSupplyCounters(): HasMany
    {
        return $this->hasMany(CartSupplyCounter::class);
    }

    public function apiUsages(): HasMany
    {
        return $this->hasMany(ApiUsage::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function videoElapsedTimes(): HasMany
    {
        return $this->hasMany(VideoElapsedTime::class);
    }

    public function serviceLogs(): MorphMany
    {
        $connection = Config::get('database.default_stats');

        return $this->setConnection($connection)->morphMany(ServiceLog::class, ServiceLog::POLYMORPHIC_NAME);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function brandDetailCounters(): HasMany
    {
        return $this->hasMany(BrandDetailCounter::class);
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, BrandDetailCounter::class)->withTimestamps();
    }

    public function ordersInProgress(): HasMany
    {
        return $this->hasMany(Order::class)->scoped(new ByLastSubstatuses(array_merge(Substatus::STATUSES_APPROVED,
            Substatus::STATUSES_COMPLETED)));
    }

    public function orderSnaps(): HasMany
    {
        return $this->hasMany(OrderSnap::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
