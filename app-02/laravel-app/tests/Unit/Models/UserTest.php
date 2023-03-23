<?php

namespace Tests\Unit\Models;

use App;
use App\Actions\Models\SettingUser\GetNotificationSetting;
use App\Constants\MediaCollectionNames;
use App\Models\Agent;
use App\Models\Authenticatable;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Device;
use App\Models\Flag;
use App\Models\HasSetting;
use App\Models\HasState;
use App\Models\InternalNotification;
use App\Models\Level;
use App\Models\LevelUser;
use App\Models\PerformsOemSearches;
use App\Models\PerformsPartSearches;
use App\Models\PerformsSupplySearches;
use App\Models\Phone;
use App\Models\PlainTag;
use App\Models\Point;
use App\Models\PushNotificationToken;
use App\Models\Setting;
use App\Models\SettingUser;
use App\Models\SupplierUser;
use App\Models\SupportCall;
use App\Models\Term;
use App\Models\TermUser;
use App\Models\User;
use App\Models\UserTaggable;
use App\Notifications\CreatePasswordNotification;
use App\Traits\HasFlags;
use Config;
use Database\Factories\AppNotificationFactory;
use Exception;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Laravel\Passport\HasApiTokens;
use Mockery;
use Notification;
use ReflectionClass;
use ReflectionException;
use Schema;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Sluggable\HasSlug;
use Storage;
use Tymon\JWTAuth\Contracts\JWTSubject;

class UserTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(User::tableName(), [
            'id',
            'email',
            'email_verified_at',
            'password',
            'remember_token',
            'photo_url',
            'uses_two_factor_auth',
            'authy_id',
            'country_code',
            'phone',
            'two_factor_reset_code',
            'current_team_id',
            'stripe_id',
            'current_billing_plan',
            'card_brand',
            'card_last_four',
            'card_country',
            'billing_address',
            'billing_address_line_2',
            'billing_city',
            'billing_state',
            'billing_zip',
            'billing_country',
            'vat_id',
            'extra_billing_information',
            'trial_ends_at',
            'last_read_announcements_at',
            'created_at',
            'updated_at',
            'user_login',
            'first_name',
            'last_name',
            'public_name',
            'legacy_password',
            'legacy_id',
            'role',
            'company',
            'hvac_supplier',
            'occupation',
            'type_of_services',
            'references',
            'address',
            'address_2',
            'city',
            'state',
            'zip',
            'country',
            'timezone',
            'accreditated',
            'employees',
            'techs_number',
            'service_manager_name',
            'service_manager_phone',
            'accreditated_at',
            'name',
            'apps',
            'group_code',
            'calls_count',
            'manuals_count',
            'call_date',
            'call_count',
            'hubspot_id',
            'registration_completed',
            'registration_completed_at',
            'access_code',
            'photo',
            'bio',
            'job_title',
            'union',
            'experience_years',
            'terms',
            'verified_at',
            'manual_download_count',
            'hat_requested',
            'disabled_at',
        ]);
    }

    /** @test */
    public function it_is_a_jwt_authenticatable()
    {
        $reflection = new ReflectionClass(User::class);
        $parent     = $reflection->getParentClass()->getName();

        $this->assertSame(Authenticatable::class, $parent);
    }

    /** @test */
    public function it_implements_interfaces()
    {
        $reflection = new ReflectionClass(User::class);

        $this->assertTrue($reflection->implementsInterface(JWTSubject::class));
        $this->assertTrue($reflection->implementsInterface(HasMedia::class));
        $this->assertTrue($reflection->implementsInterface(CanResetPasswordContract::class));
        $this->assertTrue($reflection->implementsInterface(PerformsOemSearches::class));
        $this->assertTrue($reflection->implementsInterface(PerformsPartSearches::class));
        $this->assertTrue($reflection->implementsInterface(HasSetting::class));
        $this->assertTrue($reflection->implementsInterface(PerformsSupplySearches::class));
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_traits()
    {
        $this->assertUseTrait(User::class, CausesActivity::class);
        $this->assertUseTrait(User::class, HasApiTokens::class);
        $this->assertUseTrait(User::class, Notifiable::class, ['routeNotificationForTwilio']);
        $this->assertUseTrait(User::class, HasRoles::class);
        $this->assertUseTrait(User::class, HasState::class);
        $this->assertUseTrait(User::class, InteractsWithMedia::class,
            ['registerMediaConversions', 'registerMediaCollections']);
        $this->assertUseTrait(User::class, CanResetPassword::class);
        $this->assertUseTrait(User::class, HasSlug::class, ['getSlugOptions', 'generateNonUniqueSlug']);
        $this->assertUseTrait(User::class, HasFlags::class);
    }

    /** @test */
    public function it_knows_if_is_an_agent()
    {
        $noAgent   = User::factory()->create();
        $userAgent = Agent::factory()->create()->user;

        $this->assertFalse($noAgent->isAgent());
        $this->assertTrue($userAgent->isAgent());
    }

    /** @test */
    public function it_returns_app_version_based_valid_fcm_tokens()
    {
        Config::set('notifications.push.min_app_version', '5.2.0');
        $user           = User::factory()->create();
        $validDevices   = Device::factory()->usingUser($user)->appVersion('5.2.0')->count(2)->create();
        $invalidDevices = Device::factory()->usingUser($user)->appVersion('5.1.9')->count(3)->create();
        $oldDevices     = Device::factory()->usingUser($user)->count(4)->create(['app_version' => null]);

        $validPushNotificationTokens = Collection::make([]);
        $validDevices->each(function(Device $device) use ($validPushNotificationTokens) {
            $pushNotificationToken = PushNotificationToken::factory()->usingDevice($device)->create();
            $validPushNotificationTokens->add($pushNotificationToken);
        });
        $invalidDevices->each(function(Device $device) {
            PushNotificationToken::factory()->usingDevice($device)->create();
        });
        $oldDevices->each(function(Device $device) {
            PushNotificationToken::factory()->usingDevice($device)->create();
        });

        $this->assertEquals($user->routeNotificationForFcm(), $validPushNotificationTokens->pluck('token')->toArray());
    }

    /** @test */
    public function it_knows_if_is_following_a_taggable()
    {
        $user = User::factory()->create();

        $notFollowedTaggable = PlainTag::factory()->create();
        $userTaggable        = UserTaggable::factory()->usingUser($user)->issue()->create();

        $this->assertFalse($user->isFollowing($notFollowedTaggable));
        $this->assertTrue($user->isFollowing($userTaggable->taggable));
    }

    /**
     * @test
     *
     * @param array $data
     *
     * @dataProvider dataProvider
     */
    public function it_knows_if_an_user_is_moderator(array $data)
    {
        $moderator = User::factory()->create($data);
        $plainUser = User::factory()->create();

        $this->assertTrue($moderator->isModerator());
        $this->assertFalse($plainUser->isModerator());
    }

    public function dataProvider(): array
    {
        return [
            [['email' => 'acurry@bionatusllc.com']],
            [['email' => 'dbunnett@bluon.com']],
            [['email' => 'pcapuciati@bluon.com']],
        ];
    }

    /** @test */
    public function it_return_a_collection_of_setting_users_for_all_existing_settings()
    {
        $setting     = Setting::factory()->boolean()->create(['name' => 'setting sin user']);
        $settingUser = SettingUser::factory()->create();
        $user        = $settingUser->user;

        $allSettingUsers = $user->allSettingUsers();

        $this->assertCollectionOfClass(SettingUser::class, $allSettingUsers);
        $this->assertCount(2, $allSettingUsers);

        /** @var SettingUser $nonExistingSettingUser */
        $nonExistingSettingUser = $allSettingUsers->first();
        /** @var SettingUser $existingSettingUser */
        $existingSettingUser = $allSettingUsers->last();

        $this->assertEquals($setting->value, $nonExistingSettingUser->value);
        $this->assertEquals($setting->id, $nonExistingSettingUser->setting_id);

        $this->assertEquals($settingUser->value, $existingSettingUser->value);
        $this->assertEquals($settingUser->setting_id, $existingSettingUser->setting_id);
    }

    /** @test */
    public function it_does_not_return_agent_group_setting_user_if_user_is_not_an_agent()
    {
        Setting::factory()->groupNotification()->boolean()->create();
        Setting::factory()->groupAgent()->boolean()->create();
        $user = User::factory()->create();

        $settingUsers = $user->allSettingUsers();

        $this->assertCollectionOfClass(SettingUser::class, $settingUsers);
        $this->assertCount(1, $settingUsers);

        /** @var SettingUser $settingUser */
        $settingUser = $settingUsers->first();

        $this->assertFalse($settingUser->setting->isGroupAgent());
    }

    /** @test */
    public function it_knows_its_unread_notifications_count()
    {
        $user = User::factory()->create();
        (new AppNotificationFactory())->count(7)->create();
        (new AppNotificationFactory())->count(3)->usingUser($user)->create();
        (new AppNotificationFactory())->count(10)->usingUser($user)->read()->create();
        InternalNotification::factory()->count(7)->create();
        InternalNotification::factory()->count(3)->usingUser($user)->create();
        InternalNotification::factory()->count(10)->usingUser($user)->read()->create();

        $unreadNotificationsCount = $user->getUnreadNotificationsCount();

        $this->assertEquals(6, $unreadNotificationsCount);
    }

    /** @test */
    public function it_does_not_set_a_setting_that_does_not_exist()
    {
        $setting = new Setting();

        $user = User::factory()->create();

        $this->assertNull($user->setSetting($setting, 'any'));
    }

    /** @test */
    public function it_sets_a_setting_value()
    {
        $value   = 'any';
        $setting = Setting::factory()->string()->create();
        $user    = User::factory()->create();

        $this->assertInstanceOf(SettingUser::class, $settingUser = $user->setSetting($setting, $value));

        $this->assertSame($value, $settingUser->value);
    }

    /** @test */
    public function it_returns_its_full_name()
    {
        $name             = 'Michel Doe';
        $firstName        = 'John';
        $lastName         = 'Doe';
        $fullName         = $firstName . ' ' . $lastName;
        $noOne            = new User();
        $john             = new User(['first_name' => $firstName]);
        $doe              = new User(['last_name' => $lastName]);
        $johnDoe          = new User(['first_name' => $firstName, 'last_name' => $lastName]);
        $anotherJonDoe    = new User(['name' => $fullName]);
        $priorityFullName = new User(['name' => $name, 'first_name' => $firstName, 'last_name' => $lastName]);

        $this->assertSame('', $noOne->fullName());
        $this->assertSame($firstName, $john->fullName());
        $this->assertSame($lastName, $doe->fullName());
        $this->assertSame($fullName, $johnDoe->fullName());
        $this->assertSame($fullName, $anotherJonDoe->fullName());
        $this->assertSame($fullName, $priorityFullName->fullName());
    }

    /** @test */
    public function it_returns_a_url_for_its_local_photo()
    {
        $withoutPhoto = new User();
        $withPhoto    = new User(['photo' => 'something.png']);

        $this->assertNull($withoutPhoto->photoUrl());
        $this->assertSame(asset(Storage::url($withPhoto->photo)), $withPhoto->photoUrl());
    }

    /** @test */
    public function it_knows_if_it_is_verified()
    {
        $verified   = User::factory()->verified(Carbon::now())->make();
        $unverified = User::factory()->verified(null)->make();

        $this->assertTrue($verified->isVerified());
        $this->assertFalse($unverified->isVerified());
    }

    /** @test */
    public function it_knows_if_it_is_disabled()
    {
        $disable = User::factory()->disabled(Carbon::now())->make();
        $enable  = User::factory()->disabled(null)->make();

        $this->assertTrue($disable->isDisabled());
        $this->assertFalse($enable->isDisabled());
    }

    /** @test */
    public function it_knows_if_is_accredited()
    {
        $accredited    = User::factory()->accredited()->make();
        $notAccredited = User::factory()->accredited(false)->make();
        $unknown       = User::factory()->accredited(null)->make();

        $this->assertTrue($accredited->isAccredited());
        $this->assertFalse($notAccredited->isAccredited());
        $this->assertFalse($unknown->isAccredited());
    }

    /** @test */
    public function it_knows_if_is_registered()
    {
        $registered    = User::factory()->registered()->make();
        $notRegistered = User::factory()->registered(false)->make();
        $unknown       = User::factory()->registered(null)->make();

        $this->assertTrue($registered->isRegistered());
        $this->assertFalse($notRegistered->isRegistered());
        $this->assertFalse($unknown->isRegistered());
    }

    /** @test */
    public function it_knows_if_is_support_call_disabled()
    {
        $userWithSupportCallDisabled = User::factory()->create();
        Flag::factory()->usingModel($userWithSupportCallDisabled)->create(['name' => Flag::SUPPORT_CALL_DISABLED]);
        $userWithSupportCallEnabled = User::factory()->create();

        $this->assertSame($userWithSupportCallDisabled->isSupportCallDisabled(), true);
        $this->assertSame($userWithSupportCallEnabled->isSupportCallDisabled(), false);
    }

    /** @test */
    public function it_knows_if_has_accepted_tos()
    {
        $currentTerm = Term::factory()->create();
        $accepted    = User::factory()->create();
        TermUser::factory()->usingUser($accepted)->usingTerm($currentTerm)->create();
        $notAccepted = User::factory()->create();

        $this->assertTrue($accepted->hasToSAccepted());
        $this->assertFalse($notAccepted->hasToSAccepted());
    }

    /** @test
     *
     * @dataProvider nameDataProvider
     *
     * @param string $firstName
     * @param string $lastName
     * @param string $slug
     */
    public function it_generates_unique_public_names_based_on_first_and_last_name(
        string $firstName,
        string $lastName,
        string $slug
    ) {
        User::factory()->create(['public_name' => 'ExistingUser']);
        User::factory()->create(['public_name' => 'FillingGaps']);
        User::factory()->create(['public_name' => 'FillingGaps1']);
        User::factory()->create(['public_name' => 'FillingGaps3']);

        $user = User::factory()->create(['first_name' => $firstName, 'last_name' => $lastName]);

        $user->generateSlug();

        $this->assertEquals($slug, $user->public_name);
    }

    public function nameDataProvider(): array
    {
        return [
            ['John', 'Doe', 'JohnDoe'],
            ['Existing', 'User', 'ExistingUser1'],
            ['', '', 'User'],
            ['1', '', 'User'],
            ['filling', 'gaps', 'FillingGaps2'],
            ['Charles thomas', 'Williams', 'CharlesThomasWilliams'],
            ['George W.', 'Washington', 'GeorgeWWashington'],
        ];
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_interacts_with_media_trait()
    {
        $this->assertUseTrait(User::class, InteractsWithMedia::class,
            ['registerMediaCollections', 'registerMediaConversions']);
    }

    /** @test */
    public function it_registers_images_media_collection()
    {
        $user = User::factory()->create();
        $user->registerMediaCollections();

        $mediaCollectionNames = Collection::make($user->mediaCollections)->pluck('name');
        $this->assertContains(MediaCollectionNames::IMAGES, $mediaCollectionNames);
    }

    /** @test
     *
     * @dataProvider shouldBeVerifiedDataProvider
     */
    public function it_knows_if_should_be_verified(
        bool $hasUserFields,
        bool $hasCompany,
        bool $hasSuppliers
    ) {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('shouldBeVerified')->passthru();
        $user->shouldReceive('hasUserFieldsForVerificationFilled')->once()->andReturn($hasUserFields);
        $user->shouldReceive('hasCompany')->times((int) ($hasUserFields))->andReturn($hasCompany);
        $user->shouldReceive('hasSuppliers')->times((int) ($hasUserFields && $hasCompany))->andReturn($hasSuppliers);

        $this->assertSame($hasUserFields && $hasCompany && $hasSuppliers, $user->shouldBeVerified());
    }

    public function shouldBeVerifiedDataProvider(): array
    {
        return [
            '!fields, !company, !suppliers' => [false, false, false],
            '!fields, !company, suppliers'  => [false, false, true],
            '!fields, company, !suppliers'  => [false, true, false],
            '!fields, company, suppliers'   => [false, true, true],
            'fields, !company, !suppliers'  => [true, false, false],
            'fields, !company, suppliers'   => [true, false, true],
            'fields, company, !suppliers'   => [true, true, false],
            'fields, company, suppliers'    => [true, true, true],
        ];
    }

    /** @test
     *
     * @param array $attributes
     * @param bool  $verified
     *
     * @dataProvider verifyFieldsDataProvider
     */
    public function it_knows_if_all_fields_for_verify_are_filled(array $attributes, bool $verified)
    {
        $fields = Collection::make($attributes);
        $user   = Mockery::mock(User::class);
        $user->shouldReceive('hasUserFieldsForVerificationFilled')->once()->passthru();
        $user->shouldReceive('getAttribute')->once()->with('first_name')->andReturn($fields->get('first_name'));
        $user->shouldReceive('getAttribute')->once()->with('last_name')->andReturn($fields->get('last_name'));
        $user->shouldReceive('getAttribute')->once()->with('zip')->andReturn($fields->get('zip'));
        $user->shouldReceive('getAttribute')->once()->with('address')->andReturn($fields->get('address'));
        $user->shouldReceive('getAttribute')->once()->with('country')->andReturn($fields->get('country'));
        $user->shouldReceive('getAttribute')->once()->with('state')->andReturn($fields->get('state'));
        $user->shouldReceive('getAttribute')->once()->with('city')->andReturn($fields->get('city'));

        $this->assertSame($verified, $user->hasUserFieldsForVerificationFilled());
    }

    public function verifyFieldsDataProvider(): array
    {
        $fields = [
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'zip'        => 123,
            'address'    => '742 Evergreen Terrace',
            'country'    => 'USA',
            'state'      => 'Springfield County',
            'city'       => 'Springfield',
        ];

        return [
            [
                [],
                false,
            ],
            [
                Arr::only($fields, ['last_name', 'zip', 'address', 'country', 'state', 'city']),
                false,
            ],
            [
                Arr::only($fields, ['first_name', 'zip', 'address', 'country', 'state', 'city']),
                false,
            ],
            [
                Arr::only($fields, ['first_name', 'last_name', 'address', 'country', 'state', 'city']),
                false,
            ],
            [
                Arr::only($fields, ['first_name', 'last_name', 'zip', 'country', 'state', 'city']),
                false,
            ],
            [
                Arr::only($fields, ['first_name', 'last_name', 'zip', 'address', 'state', 'city']),
                false,
            ],
            [
                Arr::only($fields, ['first_name', 'last_name', 'zip', 'address', 'country', 'city']),
                false,
            ],
            [
                Arr::only($fields, ['first_name', 'last_name', 'zip', 'address', 'country', 'state']),
                false,
            ],
            [
                Arr::only($fields, ['first_name', 'last_name', 'zip', 'address', 'country', 'state', 'city']),
                true,
            ],
        ];
    }

    /** @test */
    public function it_knows_if_has_a_company_associated()
    {
        $user        = User::factory()->create();
        $companyUser = CompanyUser::factory()->create();

        $this->assertFalse($user->hasCompany());
        $this->assertTrue($companyUser->user->hasCompany());
    }

    /** @test */
    public function it_knows_the_total_points_earned()
    {
        $user = User::factory()->create();

        $completed = Point::factory()->usingUser($user)->count(3)->createQuietly();
        $canceled  = Point::factory()->usingUser($user)->count(2)->orderCanceled()->createQuietly();
        Point::factory()->usingUser($user)->redeemed()->createQuietly();
        $expected = $completed->merge($canceled)->sum('points_earned');

        $this->assertSame($expected, $user->totalPointsEarned());
    }

    /** @test */
    public function it_knows_the_total_points_redeemed()
    {
        $user = User::factory()->create();

        $expected = Point::factory()->usingUser($user)->redeemed()->count(3)->createQuietly()->sum('points_redeemed');
        Point::factory()->usingUser($user)->createQuietly();
        Point::factory()->usingUser($user)->orderCanceled()->createQuietly();

        $this->assertSame($expected, $user->totalPointsRedeemed());
    }

    /** @test */
    public function it_knows_the_total_points_available()
    {
        $user = User::factory()->create();

        $completed = Point::factory()->usingUser($user)->count(5)->createQuietly()->sum('points_earned');
        $canceled  = Point::factory()
            ->usingUser($user)
            ->orderCanceled()
            ->count(2)
            ->createQuietly()
            ->sum('points_earned');
        $redeemed  = Point::factory()->usingUser($user)->redeemed()->count(2)->createQuietly()->sum('points_redeemed');

        $expected = $completed + $canceled - $redeemed;

        $this->assertSame($expected, $user->availablePoints());
    }

    /** @test */
    public function it_knows_the_total_points_available_converted_to_cash()
    {
        $user = User::factory()->create();

        Point::factory()->usingUser($user)->createQuietly(['points_earned' => 100]);
        Point::factory()->usingUser($user)->createQuietly(['points_earned' => 1]);
        Point::factory()->usingUser($user)->orderCanceled()->createQuietly(['points_earned' => -10]);
        Point::factory()->usingUser($user)->redeemed()->createQuietly(['points_redeemed' => 20]);

        $this->assertSame(0.71, $user->availablePointsToCash());
    }

    /** @test */
    public function it_knows_the_current_level()
    {
        $user = User::factory()->create();
        Level::factory()->create([
            'slug' => $levelSlug0 = 'level-0',
            'from' => 0,
            'to'   => 999,
        ]);
        $level1 = Level::factory()->create([
            'slug' => $levelSlug1 = 'level-1',
            'from' => 1000,
            'to'   => null,
        ]);
        $this->assertSame($levelSlug0, $user->currentLevel()->slug);
        LevelUser::factory()->usingUser($user)->usingLevel($level1)->create();
        $this->assertSame($levelSlug1, $user->currentLevel()->slug);
    }

    /** @test */
    public function it_process_the_current_level_without_support_call_disabled_flag()
    {
        $user = User::factory()->create();
        Level::factory()->create([
            'slug' => $levelSlug0 = 'level-0',
            'from' => 0,
            'to'   => 999,
        ]);
        Level::factory()->create([
            'slug' => $levelSlug1 = 'level-1',
            'from' => 1000,
            'to'   => null,
        ]);
        $this->assertSame($levelSlug0, $user->currentLevel()->slug);

        Point::factory()->usingUser($user)->createQuietly([
            'points_earned' => 1000,
        ]);

        $user->processLevel();
        $this->assertSame($levelSlug1, $user->currentLevel()->slug);

        Point::factory()->usingUser($user)->createQuietly([
            'points_earned' => -1000,
        ]);
        $user->processLevel();
        $this->assertSame($levelSlug0, $user->currentLevel()->slug);
        $this->assertFalse($user->isSupportCallDisabled());
    }

    /** @test */
    public function it_process_the_current_level_with_support_call_disabled_flag()
    {
        $user = User::factory()->create();
        $user->flag(Flag::SUPPORT_CALL_DISABLED);

        Level::factory()->create([
            'slug' => $levelSlug0 = 'level-0',
            'from' => 0,
            'to'   => 999,
        ]);
        Level::factory()->create([
            'slug' => $levelSlug1 = 'level-1',
            'from' => 1000,
            'to'   => null,
        ]);
        $this->assertSame($levelSlug0, $user->currentLevel()->slug);

        Point::factory()->usingUser($user)->createQuietly([
            'points_earned' => 1000,
        ]);

        $this->assertTrue($user->hasFlag(Flag::SUPPORT_CALL_DISABLED));

        $user->processLevel();

        $this->assertFalse($user->hasFlag(Flag::SUPPORT_CALL_DISABLED));
        $this->assertSame($levelSlug1, $user->currentLevel()->slug);

        Point::factory()->usingUser($user)->createQuietly([
            'points_earned' => -1000,
        ]);

        $user->processLevel();

        $this->assertSame($levelSlug0, $user->currentLevel()->slug);
    }

    /** @test */
    public function it_knows_if_has_a_suppliers_associated()
    {
        $user         = User::factory()->create();
        $supplierUser = SupplierUser::factory()->createQuietly();

        $this->assertFalse($user->hasSuppliers());
        $this->assertTrue($supplierUser->user->hasSuppliers());
    }

    /** @test */
    public function it_knows_if_has_a_support_calls()
    {
        $userWithSupportCalls = User::factory()->create();
        SupportCall::factory()->usingUser($userWithSupportCalls)->create();
        $userWithoutSupportCalls = User::factory()->create();

        $this->assertFalse($userWithoutSupportCalls->hasSuppliers());
        $this->assertTrue($userWithSupportCalls->hasSupportCalls());
    }

    /** @test */
    public function it_can_verify_a_user()
    {
        $user = Mockery::mock(User::class);
        $user->makePartial();

        $this->assertFalse($user->isVerified());

        $user->shouldReceive('shouldBeVerified')->withNoArgs()->once()->andReturnTrue();
        $user->verify();
        $this->assertTrue($user->isVerified());
    }

    /** @test */
    public function verify_does_not_change_the_date_on_a_verified_user()
    {
        $date = Carbon::now();
        $user = Mockery::mock(User::class);

        $user->shouldReceive('getAttribute')->with('verified_at')->atLeast()->andReturn($date);
        $user->shouldReceive('isVerified')->once()->passthru();

        $this->assertTrue($user->isVerified());
        $user->shouldReceive('shouldBeVerified')->withNoArgs()->once()->andReturnTrue();
        $user->shouldReceive('verify')->once()->passthru();
        $user->shouldReceive('offsetExists')->once()->passthru();
        $user->shouldReceive('setAttribute')->withArgs(['verified_at', $date])->once();
        $user->verify();
    }

    /** @test */
    public function verify_set_null_date_if_the_user_should_not_be_verified()
    {
        $user = Mockery::mock(User::class);

        $user->shouldReceive('shouldBeVerified')->withNoArgs()->once()->andReturnFalse();
        $user->shouldReceive('setAttribute')->withArgs(['verified_at', null])->once();
        $user->shouldReceive('verify')->once()->passthru();

        $user->verify();
    }

    /** @test
     *
     * @param string $field
     * @param string $value
     *
     * @dataProvider requiresHubspotSyncDataProvider
     */
    public function it_knows_if_it_requires_to_sync_up_to_hubspot(string $field, string $value = 'SomethingNew')
    {
        $noNeed = User::factory()->create();
        $need   = User::factory()->create();

        $need->setAttribute($field, $value);

        $this->assertFalse($noNeed->requiresHubspotSync());
        $this->assertTrue($need->requiresHubspotSync());
    }

    public function requiresHubspotSyncDataProvider(): array
    {
        return [
            ['first_name'],
            ['last_name'],
            ['email_name'],
            ['public_name'],
            ['hvac_supplier'],
            ['occupation'],
            ['apps'],
            ['service_manager_name'],
            ['service_manager_phone'],
            ['phone'],
            ['address'],
            ['address_2'],
            ['city'],
            ['state'],
            ['zip'],
            ['accreditated'],
            ['group_code'],
            ['calls_count'],
            ['manuals_count'],
            ['bio'],
            ['union'],
            ['experience_years'],
            ['company'],
            ['hat_requested'],
            ['photo'],
            ['verified_at', '2021-01-01'],
        ];
    }

    /** @test */
    public function it_knows_if_it_does_not_requires_to_sync_up_to_hubspot()
    {
        $userFields = Schema::getColumnListing(User::tableName());

        $requiresHubspotSyncCollection = Support\Collection::make($this->requiresHubspotSyncDataProvider());

        $syncFields = $requiresHubspotSyncCollection->map(fn(array $data) => $data[0]);

        $fieldsThatNotRequiresHubspotSync = array_diff($userFields, $syncFields->toArray());

        $user = Mockery::mock(User::class);
        $user->shouldReceive('requiresHubspotSync')->passthru();
        foreach ($fieldsThatNotRequiresHubspotSync as $field) {
            $user->shouldReceive('getDirty')->once()->andReturn([$field => 'A value']);
            $this->assertFalse($user->requiresHubspotSync());
        }
    }

    /** @test */
    public function it_uses_email_field_for_password_reset()
    {
        $user = Mockery::mock(User::class);

        $user->shouldReceive('getAttribute')->with('email')->once()->andReturn($email = 'email@email.com');
        $user->shouldReceive('getEmailForPasswordReset')->once()->passthru();

        $this->assertEquals($email, $user->getEmailForPasswordReset());
    }

    /** @test
     * @throws Exception
     */
    public function it_can_send_create_password_notification()
    {
        Notification::fake();

        $user = User::factory()->create();

        $user->sendCreatePasswordNotification($token = 'token');

        Notification::assertSentTo($user, CreatePasswordNotification::class,
            function(CreatePasswordNotification $notification) use ($token) {
                $this->assertEquals($token, $notification->token);

                return true;
            });
    }

    /** @test
     * @throws Exception
     */
    public function it_can_send_create_password_reset_notification()
    {
        Notification::fake();

        $user = User::factory()->create();

        $user->sendPasswordResetNotification($token = 'token');

        Notification::assertSentTo($user, ResetPassword::class, function(ResetPassword $notification) use ($token) {
            $this->assertEquals($token, $notification->token);

            return true;
        });
    }

    /**
     * @test
     */
    public function it_returns_company_name()
    {
        $companyName = 'Company user name';
        $company     = Company::factory()->create(['name' => $companyName]);
        $companyUser = CompanyUser::factory()->usingCompany($company)->create();
        $user        = $companyUser->user;
        $this->assertEquals($companyName, $user->companyName());
    }

    /**
     * @test
     */
    public function it_returns_null_as_company_name_when_user_has_no_company()
    {
        $user = User::factory()->create();
        $this->assertNull($user->companyName());
    }

    /** @test
     * @dataProvider pushNotificationsWithoutSettingDataProvider
     */
    public function it_returns_if_the_user_should_send_a_push_notification_without_setting(
        bool $enable,
        bool $config,
        bool $expected
    ) {
        Config::set('notifications.push.enabled', $config);
        $user = User::factory()->create([
            'disabled_at' => !$enable ? Carbon::now() : null,
        ]);

        $this->assertSame($expected, $user->shouldSendPushNotificationWithoutSetting('slug'));
    }

    public function pushNotificationsWithoutSettingDataProvider(): array
    {
        return [
            //enabled, config, expected
            [true, true, true],
            [true, false, false],
            [false, true, false],
            [false, false, false],
            [false, false, false],
        ];
    }

    /** @test
     * @dataProvider inAppNotificationsDataProvider
     */
    public function it_returns_if_the_user_should_send_a_in_app_notification(
        bool $disabled,
        bool $config,
        bool $setting,
        bool $expected
    ) {
        Config::set('notifications.push.enabled', $config);
        $user = User::factory()->create([
            'disabled_at' => $disabled ? Carbon::now() : null,
        ]);

        $getNotificationSetting = Mockery::mock(GetNotificationSetting::class);
        $getNotificationSetting->shouldReceive('execute')
            ->withNoArgs()
            ->times((int) (!$disabled && $config))
            ->andReturn($setting);
        App::bind(GetNotificationSetting::class, fn() => $getNotificationSetting);

        $this->assertSame($expected, $user->shouldSendInAppNotification('slug'));
    }

    public function inAppNotificationsDataProvider(): array
    {
        return [
            //disabled, config, setting, expected
            [true, true, true, false],
            [true, true, false, false],
            [true, false, true, false],
            [true, false, false, false],
            [false, true, true, true],
            [false, true, false, false],
            [false, false, true, false],
            [false, false, false, false],
        ];
    }

    /** @test
     * @dataProvider smsNotificationsWithoutSettingDataProvider
     */
    public function it_returns_if_the_user_should_send_a_sms_notification_without_setting(
        bool $phone,
        bool $config,
        bool $expected
    ) {
        Config::set('notifications.sms.enabled', $config);
        $user = User::factory()->create();

        if ($phone) {
            Phone::factory()->usingUser($user)->create();
        }

        $this->assertSame($expected, $user->shouldSendSmsNotificationWithoutSetting('slug'));
    }

    public function smsNotificationsWithoutSettingDataProvider(): array
    {
        return [
            //phone, config, expected
            [true, true, true],
            [true, false, false],
            [false, true, false],
            [false, false, false],
        ];
    }

    /** @test
     * @dataProvider smsNotificationsDataProvider
     */
    public function it_returns_if_the_user_should_send_a_sms_notification(
        bool $phone,
        bool $config,
        bool $setting,
        bool $expected
    ) {
        Config::set('notifications.sms.enabled', $config);
        $user = User::factory()->create();

        if ($phone) {
            Phone::factory()->usingUser($user)->create();
        }

        $getNotificationSetting = Mockery::mock(GetNotificationSetting::class);
        $getNotificationSetting->shouldReceive('execute')
            ->withNoArgs()
            ->times((int) ($phone && $config))
            ->andReturn($setting);
        App::bind(GetNotificationSetting::class, fn() => $getNotificationSetting);

        $this->assertSame($expected, $user->shouldSendSmsNotification('slug'));
    }

    public function smsNotificationsDataProvider(): array
    {
        return [
            //phone, config, setting, expected
            [true, true, true, true],
            [true, true, false, false],
            [true, false, true, false],
            [true, false, false, false],
            [false, true, true, false],
            [false, true, false, false],
            [false, false, true, false],
            [false, false, false, false],
        ];
    }

    /** @test
     * @dataProvider forumNotificationsDataProvider
     */
    public function it_returns_if_the_user_should_send_a_forum_notification(
        bool $disabledUSer,
        bool $setting,
        bool $userSetting,
        bool $expected
    ) {
        $user = User::factory()->create([
            'disabled_at' => $disabledUSer ? Carbon::now() : null,
        ]);

        $getNotificationSetting = Mockery::mock(GetNotificationSetting::class);

        if (!$disabledUSer) {
            $getNotificationSetting->shouldReceive('execute')->withNoArgs()->once()->andReturn($setting);
        }

        if (!$disabledUSer && !$setting) {
            $getNotificationSetting->shouldReceive('execute')->withNoArgs()->once()->andReturn($userSetting);
        }

        App::bind(GetNotificationSetting::class, fn() => $getNotificationSetting);

        $this->assertSame($expected, $user->shouldSendForumNotifications('slug'));
    }

    public function forumNotificationsDataProvider(): array
    {
        return [
            //disabled, setting, userSetting, expected
            [true, true, true, false],
            [true, true, false, false],
            [true, false, true, false],
            [true, false, false, false],
            [false, true, true, false],
            [false, true, false, false],
            [false, false, true, true],
            [false, false, false, false],
        ];
    }

    /** @test */
    public function it_returns_null_on_get_phone_if_user_doesnt_have_phone_relationship()
    {
        $user = User::factory()->create();

        $this->assertNull($user->getPhone());
    }

    /** @test */
    public function it_returns_full_number_on_get_phone_if_user_has_phone_relationship()
    {
        $user  = User::factory()->create();
        $phone = Phone::factory()->usingUser($user)->create();

        $expected = $phone->country_code . $phone->number;

        $this->assertSame($expected, $user->getPhone());
    }

    /** @test */
    public function it_calls_get_phone_method_on_route_notification_for_twilio()
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('getPhone')->withNoArgs()->once();

        $user->routeNotificationForTwilio();
    }
}
