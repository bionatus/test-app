<?php

namespace Tests\Feature\LiveApi\V2\Supplier;

use App;
use App\Constants\MediaCollectionNames;
use App\Constants\RequestKeys;
use App\Constants\RouteNames\LiveApiV2;
use App\Http\Controllers\LiveApi\V2\SupplierController;
use App\Http\Requests\LiveApi\V2\Supplier\UpdateRequest;
use App\Http\Resources\LiveApi\V2\Supplier\BaseResource;
use App\Models\Setting;
use App\Models\SettingStaff;
use App\Models\Staff;
use App\Models\Supplier;
use App\Services\Hubspot\Hubspot;
use App\Types\CountryDataType;
use Auth;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\State;
use Mockery;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see SupplierController */
class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = LiveApiV2::LIVE_API_V2_SUPPLIER_UPDATE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, UpdateRequest::class);
    }

    /** @test */
    public function it_updates_the_supplier_data()
    {
        $image    = UploadedFile::fake()->image('image.jpeg');
        $logo     = UploadedFile::fake()->image('logo.jpeg');
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->create();

        Setting::factory()->groupNotification()->boolean()->create(['slug' => Setting::SLUG_STAFF_EMAIL_NOTIFICATION]);
        Setting::factory()->groupNotification()->boolean()->create(['slug' => Setting::SLUG_STAFF_SMS_NOTIFICATION]);
        Setting::factory()->groupValidation()->boolean()->create(['slug' => Setting::SLUG_BID_NUMBER_REQUIRED]);

        Supplier::flushEventListeners();
        Auth::shouldUse('live');
        $this->login($staff);

        $country = Country::build(CountryDataType::UNITED_STATES);
        /** @var State $state */
        $state = $country->getStates()->first();

        $route = URL::route($this->routeName);

        $response = $this->patch($route, [
            RequestKeys::NAME                    => $name = 'Acme Inc.',
            RequestKeys::EMAIL                   => $email = 'acme@inc.com',
            RequestKeys::BRANCH                  => $branch = 1,
            RequestKeys::ADDRESS                 => $address = '1313 Evergreen St.',
            RequestKeys::ADDRESS_2               => $address2 = 'Unit 2',
            RequestKeys::ZIP_CODE                => $zipCode = '90210',
            RequestKeys::CITY                    => $city = 'Beverly Hills',
            RequestKeys::STATE                   => $stateCode = $state->isoCode,
            RequestKeys::COUNTRY                 => $countryCode = $country->code,
            RequestKeys::TIMEZONE                => $timezone = 'America/New_York',
            RequestKeys::PHONE                   => $phone = '5552228',
            RequestKeys::PROKEEP_PHONE           => $prokeepPhone = '5551234',
            RequestKeys::ABOUT                   => $about = 'About',
            RequestKeys::CONTACT_EMAIL           => $contactEmail = 'contact@email.com',
            RequestKeys::CONTACT_SECONDARY_EMAIL => $contactSecondaryEmail = 'contact_secondary@email.com',
            RequestKeys::CONTACT_PHONE           => $contactPhone = '555123456',
            RequestKeys::MANAGER_NAME            => $managerName = 'manager',
            RequestKeys::MANAGER_EMAIL           => $managerEmail = 'manager@email.com',
            RequestKeys::MANAGER_PHONE           => $managerPhone = '555222810',
            RequestKeys::ACCOUNTANT_NAME         => $accountantName = 'accountant',
            RequestKeys::ACCOUNTANT_EMAIL        => $accountantEmail = 'accountant@email.com',
            RequestKeys::ACCOUNTANT_PHONE        => $accountantPhone = '5557890',
            RequestKeys::COUNTER_STAFF           => [
                [
                    'name'  => $counterStaffName = 'Jane Doe',
                    'email' => $counterStaffEmail = 'counter_staff@email.com',
                ],
            ],
            RequestKeys::OFFERS_DELIVERY         => $delivery = true,
            RequestKeys::IMAGE                   => $image,
            RequestKeys::LOGO                    => $logo,
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $this->assertDatabaseHas(Supplier::tableName(), [
            'name'                    => $name,
            'email'                   => $email,
            'branch'                  => $branch,
            'phone'                   => $phone,
            'prokeep_phone'           => $prokeepPhone,
            'address'                 => $address,
            'address_2'               => $address2,
            'zip_code'                => $zipCode,
            'city'                    => $city,
            'state'                   => $stateCode,
            'country'                 => $countryCode,
            'timezone'                => $timezone,
            'about'                   => $about,
            'contact_email'           => $contactEmail,
            'contact_secondary_email' => $contactSecondaryEmail,
            'contact_phone'           => $contactPhone,
            'offers_delivery'         => $delivery,
        ]);

        $this->assertDatabaseHas(Staff::tableName(), [
            'type'  => Staff::TYPE_MANAGER,
            'name'  => $managerName,
            'email' => $managerEmail,
            'phone' => $managerPhone,
        ]);

        $this->assertDatabaseHas(Staff::tableName(), [
            'type'  => Staff::TYPE_ACCOUNTANT,
            'name'  => $accountantName,
            'email' => $accountantEmail,
            'phone' => $accountantPhone,
        ]);

        $this->assertDatabaseHas(Staff::tableName(), [
            'type'  => Staff::TYPE_COUNTER,
            'name'  => $counterStaffName,
            'email' => $counterStaffEmail,
        ]);

        $data = Collection::make($response->json('data'));
        $this->assertSame($supplier->getRouteKey(), $data['id']);

        $this->assertTrue($supplier->hasMedia(MediaCollectionNames::IMAGES));
        $this->assertCount(1, $supplier->getMedia(MediaCollectionNames::IMAGES));

        $this->assertTrue($supplier->hasMedia(MediaCollectionNames::LOGO));
        $this->assertCount(1, $supplier->getMedia(MediaCollectionNames::LOGO));
    }

    /** @test */
    public function it_should_replace_the_image_of_the_supplier_if_it_already_has_one()
    {
        $diskName = Config::get('media-library.disk_name');
        $oldImage = UploadedFile::fake()->image('old-image.jpeg');
        $image    = UploadedFile::fake()->image('image.jpeg');

        Setting::factory()->groupNotification()->boolean()->create(['slug' => Setting::SLUG_STAFF_EMAIL_NOTIFICATION]);
        Setting::factory()->groupNotification()->boolean()->create(['slug' => Setting::SLUG_STAFF_SMS_NOTIFICATION]);
        Setting::factory()->groupValidation()->boolean()->create(['slug' => Setting::SLUG_BID_NUMBER_REQUIRED]);

        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->create();

        Supplier::flushEventListeners();

        try {
            $supplier->addMedia($oldImage)->toMediaCollection(MediaCollectionNames::IMAGES);
        } catch (FileDoesNotExist|FileIsTooBig $exception) {
            // Silently ignored
        }

        $this->assertTrue($supplier->hasMedia(MediaCollectionNames::IMAGES));
        $this->assertCount(1, $supplier->getMedia(MediaCollectionNames::IMAGES));

        Auth::shouldUse('live');
        $this->login($staff);

        $route = URL::route($this->routeName);

        $response = $this->patch($route, [
            RequestKeys::NAME             => 'Acme Inc.',
            RequestKeys::EMAIL            => 'acme@inc.com',
            RequestKeys::BRANCH           => 1,
            RequestKeys::PHONE            => '5551234',
            RequestKeys::ADDRESS          => '1313 Evergreen St.',
            RequestKeys::COUNTRY          => 'US',
            RequestKeys::TIMEZONE         => 'America/New_York',
            RequestKeys::STATE            => 'US-AR',
            RequestKeys::CITY             => 'Beverly Hills',
            RequestKeys::ZIP_CODE         => '90210',
            RequestKeys::CONTACT_EMAIL    => 'contact@email.com',
            RequestKeys::CONTACT_PHONE    => '555123456',
            RequestKeys::MANAGER_NAME     => 'manager',
            RequestKeys::MANAGER_EMAIL    => 'manager@email.com',
            RequestKeys::MANAGER_PHONE    => '555222810',
            RequestKeys::ACCOUNTANT_NAME  => 'accountant',
            RequestKeys::ACCOUNTANT_EMAIL => 'accountant@email.com',
            RequestKeys::ACCOUNTANT_PHONE => '5557890',
            RequestKeys::COUNTER_STAFF    => [
                [
                    RequestKeys::NAME  => 'Jane Doe',
                    RequestKeys::EMAIL => 'counter_staff@email.com',
                ],
            ],
            RequestKeys::IMAGE            => $image,
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $supplier->load('media');
        $this->assertTrue($supplier->hasMedia(MediaCollectionNames::IMAGES));
        $this->assertCount(1, $supplier->getMedia(MediaCollectionNames::IMAGES));

        $media = $supplier->getFirstMedia(MediaCollectionNames::IMAGES);
        $this->assertSame($image->getClientOriginalName(), $media->file_name);
        $pathGenerator = PathGeneratorFactory::create($media);
        Storage::disk($diskName)->assertExists($pathGenerator->getPath($media) . $media->file_name);
    }

    /** @test */
    public function it_should_replace_the_logo_of_the_supplier_if_it_already_has_one()
    {
        $diskName = Config::get('media-library.disk_name');
        $oldLogo  = UploadedFile::fake()->image('old-logo.jpeg');
        $logo     = UploadedFile::fake()->image('logo.jpeg');
        Setting::factory()->groupNotification()->boolean()->create(['slug' => Setting::SLUG_STAFF_EMAIL_NOTIFICATION]);
        Setting::factory()->groupNotification()->boolean()->create(['slug' => Setting::SLUG_STAFF_SMS_NOTIFICATION]);
        Setting::factory()->groupValidation()->boolean()->create(['slug' => Setting::SLUG_BID_NUMBER_REQUIRED]);

        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->create();
        Supplier::flushEventListeners();

        try {
            $supplier->addMedia($oldLogo)->toMediaCollection(MediaCollectionNames::LOGO);
        } catch (FileDoesNotExist|FileIsTooBig $exception) {
            // Silently ignored
        }
        $this->assertTrue($supplier->hasMedia(MediaCollectionNames::LOGO));
        $this->assertCount(1, $supplier->getMedia(MediaCollectionNames::LOGO));

        Auth::shouldUse('live');
        $this->login($staff);

        $route = URL::route($this->routeName);

        $response = $this->patch($route, [
            RequestKeys::NAME             => 'Acme Inc.',
            RequestKeys::EMAIL            => 'acme@inc.com',
            RequestKeys::BRANCH           => 1,
            RequestKeys::PHONE            => '5551234',
            RequestKeys::ADDRESS          => '1313 Evergreen St.',
            RequestKeys::COUNTRY          => 'US',
            RequestKeys::TIMEZONE         => 'America/New_York',
            RequestKeys::STATE            => 'US-AR',
            RequestKeys::CITY             => 'Beverly Hills',
            RequestKeys::ZIP_CODE         => '90210',
            RequestKeys::CONTACT_EMAIL    => 'contact@email.com',
            RequestKeys::CONTACT_PHONE    => '555123456',
            RequestKeys::MANAGER_NAME     => 'manager',
            RequestKeys::MANAGER_EMAIL    => 'manager@email.com',
            RequestKeys::MANAGER_PHONE    => '555222810',
            RequestKeys::ACCOUNTANT_NAME  => 'accountant',
            RequestKeys::ACCOUNTANT_EMAIL => 'accountant@email.com',
            RequestKeys::ACCOUNTANT_PHONE => '5557890',
            RequestKeys::COUNTER_STAFF    => [
                [
                    'name'  => 'Jane Doe',
                    'email' => 'counter_staff@email.com',
                ],
            ],
            RequestKeys::LOGO             => $logo,
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $supplier->load('media');
        $this->assertTrue($supplier->hasMedia(MediaCollectionNames::LOGO));
        $this->assertCount(1, $supplier->getMedia(MediaCollectionNames::LOGO));

        $media = $supplier->getFirstMedia(MediaCollectionNames::LOGO);
        $this->assertSame($logo->getClientOriginalName(), $media->file_name);
        $pathGenerator = PathGeneratorFactory::create($media);
        Storage::disk($diskName)->assertExists($pathGenerator->getPath($media) . $media->file_name);
    }

    /** @test */
    public function it_should_update_email_in_staff_data()
    {
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->create(['email' => 'removable@email.com']);
        Setting::factory()->groupNotification()->boolean()->create(['slug' => Setting::SLUG_STAFF_EMAIL_NOTIFICATION]);
        Setting::factory()->groupNotification()->boolean()->create(['slug' => Setting::SLUG_STAFF_SMS_NOTIFICATION]);
        Setting::factory()->groupValidation()->boolean()->create(['slug' => Setting::SLUG_BID_NUMBER_REQUIRED]);
        Supplier::flushEventListeners();
        Auth::shouldUse('live');
        Supplier::flushEventListeners();
        $this->login($staff);

        $country = Country::build(CountryDataType::UNITED_STATES);
        /** @var State $state */
        $state = $country->getStates()->first();

        $route = URL::route($this->routeName);

        $response = $this->patch($route, [
            RequestKeys::NAME             => 'Acme Inc.',
            RequestKeys::EMAIL            => $email = 'acme@inc.com',
            RequestKeys::BRANCH           => 1,
            RequestKeys::ADDRESS          => '1313 Evergreen St.',
            RequestKeys::ZIP_CODE         => '90210',
            RequestKeys::CITY             => 'Beverly Hills',
            RequestKeys::STATE            => $state->isoCode,
            RequestKeys::COUNTRY          => $country->code,
            RequestKeys::TIMEZONE         => 'America/New_York',
            RequestKeys::PHONE            => '5552228',
            RequestKeys::CONTACT_EMAIL    => 'contact@email.com',
            RequestKeys::CONTACT_PHONE    => '555123456',
            RequestKeys::MANAGER_NAME     => 'manager',
            RequestKeys::MANAGER_EMAIL    => 'manager@email.com',
            RequestKeys::MANAGER_PHONE    => '555222810',
            RequestKeys::ACCOUNTANT_NAME  => 'accountant',
            RequestKeys::ACCOUNTANT_EMAIL => 'accountant@email.com',
            RequestKeys::ACCOUNTANT_PHONE => '5557890',
            RequestKeys::COUNTER_STAFF    => [
                [
                    'name'  => 'Jane Doe',
                    'email' => 'counter_staff@email.com',
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $this->assertDatabaseHas(Staff::tableName(), [
            'type'        => Staff::TYPE_OWNER,
            'supplier_id' => $supplier->getKey(),
            'email'       => $email,
        ]);
    }

    /** @test */
    public function it_returns_updated_data_for_accountant_manager_and_staff()
    {
        $this->markTestSkipped('It was unable to test when the supplier model needs to be refreshed for this test to pass. The will not fail even if the model was not refreshed.');

        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->create(['email' => 'removable@email.com']);
        Setting::factory()->groupNotification()->boolean()->create(['slug' => Setting::SLUG_STAFF_SMS_NOTIFICATION]);
        $country = Country::build(CountryDataType::UNITED_STATES);
        /** @var State $state */
        $state   = $country->getStates()->first();
        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->andReturnSelf();
        App::bind(Hubspot::class, fn() => $hubspot);

        Auth::shouldUse('live');
        Supplier::flushEventListeners();
        $this->login($staff);

        $route = URL::route($this->routeName);

        $response = $this->patch($route, [
            RequestKeys::NAME             => 'Acme Inc.',
            RequestKeys::EMAIL            => 'acme@inc.com',
            RequestKeys::BRANCH           => 1,
            RequestKeys::ADDRESS          => '1313 Evergreen St.',
            RequestKeys::ZIP_CODE         => '90210',
            RequestKeys::CITY             => 'Beverly Hills',
            RequestKeys::STATE            => $state->isoCode,
            RequestKeys::COUNTRY          => $country->code,
            RequestKeys::TIMEZONE         => 'America/New_York',
            RequestKeys::PHONE            => '5552228',
            RequestKeys::CONTACT_EMAIL    => 'contact@email.com',
            RequestKeys::CONTACT_PHONE    => '555123456',
            RequestKeys::MANAGER_NAME     => 'manager',
            RequestKeys::MANAGER_EMAIL    => 'manager@email.com',
            RequestKeys::MANAGER_PHONE    => '555222810',
            RequestKeys::ACCOUNTANT_NAME  => 'accountant',
            RequestKeys::ACCOUNTANT_EMAIL => 'accountant@email.com',
            RequestKeys::ACCOUNTANT_PHONE => '5557890',
            RequestKeys::COUNTER_STAFF    => [
                [
                    'name'  => 'Jane Doe',
                    'email' => 'counter_staff@email.com',
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $this->assertNotNull($response->json('data.manager_name'));
        $this->assertNotNull($response->json('data.manager_email'));
        $this->assertNotNull($response->json('data.manager_phone'));
        $this->assertNotNull($response->json('data.accountant_name'));
        $this->assertNotNull($response->json('data.accountant_email'));
        $this->assertNotNull($response->json('data.accountant_phone'));
        $this->assertNotNull($response->json('data.accountant_phone'));
        $this->assertNotNull($response->json('data.counter_staff.0.name'));
        $this->assertNotNull($response->json('data.counter_staff.0.email'));
        $this->assertNotNull($response->json('data.counter_staff.0.sms_notification'));
    }

    /** @test */
    public function it_should_create_the_counter_staff_when_not_exists()
    {
        $email    = 'acme_staff@inc.com';
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->withEmail()->create();

        $emailSetting = Setting::factory()
            ->groupNotification()
            ->boolean()
            ->create(['slug' => Setting::SLUG_STAFF_EMAIL_NOTIFICATION]);
        $smsSetting   = Setting::factory()
            ->groupNotification()
            ->boolean()
            ->create(['slug' => Setting::SLUG_STAFF_SMS_NOTIFICATION]);
        Setting::factory()->groupValidation()->boolean()->create(['slug' => Setting::SLUG_BID_NUMBER_REQUIRED]);
        Supplier::flushEventListeners();
        Auth::shouldUse('live');
        Supplier::flushEventListeners();
        $this->login($staff);

        $country = Country::build(CountryDataType::UNITED_STATES);
        /** @var State $state */
        $state = $country->getStates()->first();

        $route = URL::route($this->routeName);

        $response = $this->patch($route, [
            RequestKeys::NAME             => 'Acme Inc.',
            RequestKeys::EMAIL            => 'acme@inc.com',
            RequestKeys::BRANCH           => 1,
            RequestKeys::ADDRESS          => '1313 Evergreen St.',
            RequestKeys::ZIP_CODE         => '90210',
            RequestKeys::CITY             => 'Beverly Hills',
            RequestKeys::STATE            => $state->isoCode,
            RequestKeys::COUNTRY          => $country->code,
            RequestKeys::TIMEZONE         => 'America/New_York',
            RequestKeys::PHONE            => '5552228',
            RequestKeys::CONTACT_EMAIL    => 'contact@email.com',
            RequestKeys::CONTACT_PHONE    => '555123456',
            RequestKeys::MANAGER_NAME     => 'manager',
            RequestKeys::MANAGER_EMAIL    => 'manager@email.com',
            RequestKeys::MANAGER_PHONE    => '555222810',
            RequestKeys::ACCOUNTANT_NAME  => 'accountant',
            RequestKeys::ACCOUNTANT_EMAIL => 'accountant@email.com',
            RequestKeys::ACCOUNTANT_PHONE => '5557890',
            RequestKeys::COUNTER_STAFF    => [
                [
                    'name'               => 'Jane Doe',
                    'email'              => $email,
                    'email_notification' => true,
                    'sms_notification'   => true,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $this->assertDatabaseHas(Staff::tableName(), [
            'email' => $email,
            'type'  => Staff::TYPE_COUNTER,
        ]);

        $staff = Staff::where('email', $email)->first();
        $this->assertDatabaseHas(SettingStaff::tableName(), [
            'setting_id' => $emailSetting->getKey(),
            'staff_id'   => $staff->getKey(),
            'value'      => 1,
        ]);

        $this->assertDatabaseHas(SettingStaff::tableName(), [
            'setting_id' => $smsSetting->getKey(),
            'staff_id'   => $staff->getKey(),
            'value'      => 1,
        ]);
    }

    /** @test */
    public function it_should_update_the_counter_staff_when_exist()
    {
        $email        = 'acme_staff@inc.com';
        $name         = 'Jane Doe';
        $phone        = '123456789';
        $supplier     = Supplier::factory()->createQuietly();
        $staff        = Staff::factory()->usingSupplier($supplier)->withEmail()->create();
        $staffUpdated = Staff::factory()->usingSupplier($supplier)->counter()->create([
            'email' => $email,
            'name'  => 'John Doe',
        ]);

        $emailSetting = Setting::factory()
            ->groupNotification()
            ->boolean()
            ->create(['slug' => Setting::SLUG_STAFF_EMAIL_NOTIFICATION, 'value' => false]);
        $smsSetting   = Setting::factory()
            ->groupNotification()
            ->boolean()
            ->create(['slug' => Setting::SLUG_STAFF_SMS_NOTIFICATION, 'value' => false]);

        SettingStaff::factory()->usingStaff($staffUpdated)->usingSetting($emailSetting)->create(['value' => false]);
        SettingStaff::factory()->usingStaff($staffUpdated)->usingSetting($smsSetting)->create(['value' => false]);
        Setting::factory()->groupValidation()->boolean()->create(['slug' => Setting::SLUG_BID_NUMBER_REQUIRED]);

        Supplier::flushEventListeners();
        Auth::shouldUse('live');
        Supplier::flushEventListeners();
        $this->login($staff);

        $country = Country::build(CountryDataType::UNITED_STATES);
        /** @var State $state */
        $state = $country->getStates()->first();

        $route = URL::route($this->routeName);

        $response = $this->patch($route, [
            RequestKeys::NAME             => 'Acme Inc.',
            RequestKeys::EMAIL            => 'acme@inc.com',
            RequestKeys::BRANCH           => 1,
            RequestKeys::ADDRESS          => '1313 Evergreen St.',
            RequestKeys::ZIP_CODE         => '90210',
            RequestKeys::CITY             => 'Beverly Hills',
            RequestKeys::STATE            => $state->isoCode,
            RequestKeys::COUNTRY          => $country->code,
            RequestKeys::TIMEZONE         => 'America/New_York',
            RequestKeys::PHONE            => '5552228',
            RequestKeys::CONTACT_EMAIL    => 'contact@email.com',
            RequestKeys::CONTACT_PHONE    => '555123456',
            RequestKeys::MANAGER_NAME     => 'manager',
            RequestKeys::MANAGER_EMAIL    => 'manager@email.com',
            RequestKeys::MANAGER_PHONE    => '555222810',
            RequestKeys::ACCOUNTANT_NAME  => 'accountant',
            RequestKeys::ACCOUNTANT_EMAIL => 'accountant@email.com',
            RequestKeys::ACCOUNTANT_PHONE => '5557890',
            RequestKeys::COUNTER_STAFF    => [
                [
                    'name'               => $name,
                    'email'              => $email,
                    'phone'              => $phone,
                    'email_notification' => 1,
                    'sms_notification'   => 1,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $this->assertDatabaseHas(Staff::tableName(), [
            'email' => $email,
            'name'  => $name,
            'phone' => $phone,
            'type'  => Staff::TYPE_COUNTER,
        ]);

        $this->assertDatabaseMissing(Staff::tableName(), [
            'email' => $email,
            'name'  => 'John Doe',
            'type'  => Staff::TYPE_COUNTER,
        ]);

        $this->assertDatabaseHas(SettingStaff::tableName(), [
            'setting_id' => $emailSetting->getKey(),
            'staff_id'   => $staffUpdated->getKey(),
            'value'      => 1,
        ]);

        $this->assertDatabaseHas(SettingStaff::tableName(), [
            'setting_id' => $smsSetting->getKey(),
            'staff_id'   => $staffUpdated->getKey(),
            'value'      => 1,
        ]);
    }

    /** @test */
    public function it_should_delete_the_staff_when_not_exist_in_the_form()
    {
        $email        = 'acme_staff@inc.com';
        $supplier     = Supplier::factory()->createQuietly();
        $staff        = Staff::factory()->usingSupplier($supplier)->withEmail()->create();
        $staffDeleted = Staff::factory()->usingSupplier($supplier)->counter()->create(['email' => $email]);

        $emailSetting = Setting::factory()
            ->groupNotification()
            ->boolean()
            ->create(['slug' => Setting::SLUG_STAFF_EMAIL_NOTIFICATION]);
        $smsSetting   = Setting::factory()
            ->groupNotification()
            ->boolean()
            ->create(['slug' => Setting::SLUG_STAFF_SMS_NOTIFICATION]);
        SettingStaff::factory()->usingStaff($staffDeleted)->usingSetting($emailSetting)->create(['value' => false]);
        SettingStaff::factory()->usingStaff($staffDeleted)->usingSetting($smsSetting)->create(['value' => false]);
        Setting::factory()->groupValidation()->boolean()->create(['slug' => Setting::SLUG_BID_NUMBER_REQUIRED]);

        Supplier::flushEventListeners();
        Auth::shouldUse('live');
        Supplier::flushEventListeners();
        $this->login($staff);

        $country = Country::build(CountryDataType::UNITED_STATES);
        /** @var State $state */
        $state = $country->getStates()->first();

        $route = URL::route($this->routeName);

        $response = $this->patch($route, [
            RequestKeys::NAME             => 'Acme Inc.',
            RequestKeys::EMAIL            => 'acme@inc.com',
            RequestKeys::BRANCH           => 1,
            RequestKeys::ADDRESS          => '1313 Evergreen St.',
            RequestKeys::ZIP_CODE         => '90210',
            RequestKeys::CITY             => 'Beverly Hills',
            RequestKeys::STATE            => $state->isoCode,
            RequestKeys::COUNTRY          => $country->code,
            RequestKeys::TIMEZONE         => 'America/New_York',
            RequestKeys::PHONE            => '5552228',
            RequestKeys::CONTACT_EMAIL    => 'contact@email.com',
            RequestKeys::CONTACT_PHONE    => '555123456',
            RequestKeys::MANAGER_NAME     => 'manager',
            RequestKeys::MANAGER_EMAIL    => 'manager@email.com',
            RequestKeys::MANAGER_PHONE    => '555222810',
            RequestKeys::ACCOUNTANT_NAME  => 'accountant',
            RequestKeys::ACCOUNTANT_EMAIL => 'accountant@email.com',
            RequestKeys::ACCOUNTANT_PHONE => '5557890',
            RequestKeys::COUNTER_STAFF    => [
                [
                    'name'  => 'John Doe',
                    'email' => 'john.doe@test.com',
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $this->assertDatabaseMissing(Staff::tableName(), [
            'id'    => $staffDeleted->getKey(),
            'email' => $email,
            'type'  => Staff::TYPE_COUNTER,
        ]);

        $this->assertDatabaseMissing(SettingStaff::tableName(), [
            'setting_id' => $emailSetting->getKey(),
            'staff_id'   => $staffDeleted->getKey(),
            'value'      => 1,
        ]);

        $this->assertDatabaseMissing(SettingStaff::tableName(), [
            'setting_id' => $smsSetting->getKey(),
            'staff_id'   => $staffDeleted->getKey(),
            'value'      => 1,
        ]);
    }
}
