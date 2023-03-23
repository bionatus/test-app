<?php

namespace Tests\Feature\Api\V4\Account\Profile;

use App\Constants\MediaCollectionNames;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Events\User\CompanyUpdated;
use App\Events\User\HatRequested;
use App\Events\User\HubspotFieldUpdated;
use App\Http\Controllers\Api\V4\Account\ProfileController;
use App\Http\Requests\Api\V4\Account\Profile\UpdateRequest;
use App\Http\Resources\Api\V4\Account\Profile\BaseResource;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Phone;
use App\Models\SupplierUser;
use App\Models\User;
use App\Types\CompanyDataType;
use App\Types\CountryDataType;
use Config;
use Event;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\State;
use ReflectionProperty;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see ProfileController */
class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V4_ACCOUNT_PROFILE_UPDATE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->patch(URL::route($this->routeName));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, UpdateRequest::class);
    }

    /** @test
     * @throws Exception
     */
    public function it_should_replace_the_photo_of_a_user_profile_if_it_already_has_a_photo()
    {
        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        $file = UploadedFile::fake()->image('avatar.jpeg');

        $user     = User::factory()->create();
        $oldImage = $user->addMedia($file)
            ->preservingOriginal()
            ->usingName('old_avatar.jpeg')
            ->toMediaCollection(MediaCollectionNames::IMAGES);

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->patch($route, [
            RequestKeys::PHOTO => $file,
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $this->assertTrue($user->hasMedia(MediaCollectionNames::IMAGES));
        $this->assertCount(1, $user->getMedia(MediaCollectionNames::IMAGES));

        $media = $user->getFirstMedia(MediaCollectionNames::IMAGES);
        $this->assertSame($file->getClientOriginalName(), $media->file_name);
        $pathGenerator = PathGeneratorFactory::create($media);
        Storage::disk($diskName)->assertExists($pathGenerator->getPath($media) . $media->file_name);

        $this->assertDeleted($oldImage);
        $pathGenerator = PathGeneratorFactory::create($oldImage);
        Storage::disk($diskName)->assertMissing($pathGenerator->getPath($oldImage) . $oldImage->file_name);
    }

    /** @test */
    public function it_should_sync_the_photo_with_the_former_user_model()
    {
        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        Storage::fake('public');
        $file = UploadedFile::fake()->image('avatar.jpeg');
        $user = User::factory()->create();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->patch($route, [
            RequestKeys::PHOTO => $file,
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $media = $user->getFirstMedia(MediaCollectionNames::IMAGES);
        $this->assertSame($file->getClientOriginalName(), $media->file_name);
        $pathGenerator = PathGeneratorFactory::create($media);
        Storage::disk($diskName)->assertExists($pathGenerator->getPath($media) . $media->file_name);

        $user->refresh();
        $this->assertNotNull($user->photo);
        Storage::disk('public')->assertExists($user->photo);
    }

    /** @test */
    public function it_should_delete_the_former_user_model_photo_if_exists()
    {
        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        Storage::fake('public');
        $file = UploadedFile::fake()->image('avatar.jpeg');
        Storage::disk('public')->put($oldPhotoName = 'foo.jpeg', '');
        $user = User::factory()->create(['photo' => $oldPhotoName]);

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->patch($route, [
            RequestKeys::PHOTO => $file,
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $user->refresh();
        $this->assertNotNull($user->photo);
        Storage::disk('public')->assertExists($user->photo);
        Storage::disk('public')->assertMissing($oldPhotoName);
    }

    /** @test */
    public function it_updates_the_user_data()
    {
        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        $file = UploadedFile::fake()->image('avatar.jpeg');

        $user    = User::factory()->create();
        $country = Country::build(CountryDataType::UNITED_STATES);
        /** @var State $state */
        $state = $country->getStates()->first();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->patch($route, [
            RequestKeys::PHOTO       => $file,
            RequestKeys::FIRST_NAME  => $firstName = 'John',
            RequestKeys::LAST_NAME   => $lastName = 'Doe',
            RequestKeys::PUBLIC_NAME => $publicName = 'JohnDoe',
            RequestKeys::ZIP_CODE    => $zipCode = '90210',
            RequestKeys::ADDRESS     => $address = '1313 Evergreen St.',
            RequestKeys::ADDRESS_2   => $address2 = 'Unit 2',
            RequestKeys::CITY        => $city = 'Beverly Hills',
            RequestKeys::STATE       => $stateCode = $state->isoCode,
            RequestKeys::COUNTRY     => $countryCode = $country->code,
            RequestKeys::EXPERIENCE  => $experience = '6',
            RequestKeys::BIO         => $bio = 'Lorem ipsum',
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $this->assertDatabaseHas(User::tableName(), [
            'first_name'       => $firstName,
            'last_name'        => $lastName,
            'public_name'      => $publicName,
            'zip'              => $zipCode,
            'address'          => $address,
            'address_2'        => $address2,
            'city'             => $city,
            'state'            => $stateCode,
            'country'          => $countryCode,
            'experience_years' => $experience,
            'bio'              => $bio,
        ]);

        $this->assertTrue($user->hasMedia(MediaCollectionNames::IMAGES));
        $this->assertCount(1, $user->getMedia(MediaCollectionNames::IMAGES));

        $media         = $user->getFirstMedia(MediaCollectionNames::IMAGES);
        $pathGenerator = PathGeneratorFactory::create($media);
        Storage::disk($diskName)->assertExists($pathGenerator->getPath($media) . $media->file_name);

        $data = Collection::make($response->json('data'));
        $this->assertSame($user->getKey(), $data['id']);
    }

    /** @test */
    public function it_updates_the_company_user_data()
    {
        $user    = User::factory()->create();
        $company = Company::factory()->create([
            'type' => CompanyDataType::TYPE_TRADE_SCHOOL,
        ]);
        CompanyUser::factory()->usingCompany($company)->usingUser($user)->create();
        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->patch($route, [
            RequestKeys::COMPANY                => $company->getRouteKey(),
            RequestKeys::JOB_TITLE              => $jobTitle = CompanyDataType::getJobTitles(CompanyDataType::TYPE_TRADE_SCHOOL)[1],
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => $primaryEquipmentType = CompanyDataType::EQUIPMENT_TYPE_INDUSTRIAL,
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $this->assertDatabaseHas(CompanyUser::tableName(), [
            'user_id'        => $user->getKey(),
            'job_title'      => $jobTitle,
            'equipment_type' => $primaryEquipmentType,
        ]);

        $data = Collection::make($response->json('data'));
        $this->assertSame($user->getKey(), $data['id']);
    }

    /** @test */
    public function it_dispatches_an_event_if_user_changed()
    {
        Event::fake(HubspotFieldUpdated::class);

        $user = User::factory()->create();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->patch($route, [
            RequestKeys::FIRST_NAME => 'John',
            RequestKeys::LAST_NAME  => 'Doe',
            RequestKeys::ZIP_CODE   => '90210',
        ]);

        $response->assertStatus(Response::HTTP_OK);

        Event::assertDispatched(function(HubspotFieldUpdated $event) use ($user) {
            $property = new ReflectionProperty($event, 'user');
            $property->setAccessible(true);
            $this->assertSame($user->getKey(), $property->getValue($event)->getKey());

            return true;
        });
    }

    /** @test */
    public function it_does_not_dispatches_an_event_if_user_has_not_changed()
    {
        Event::fake(HubspotFieldUpdated::class);

        $user = User::factory()->create();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->patch($route, []);

        $response->assertStatus(Response::HTTP_OK);

        Event::assertNotDispatched(HubspotFieldUpdated::class);
    }

    /** @test
     * @dataProvider hatRequestedProvider
     */
    public function it_can_request_or_decline_a_hat_if_has_not_previously_done_so(bool $requested)
    {
        Event::fake([HatRequested::class]);
        $user = User::factory()->create();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->patch($route, [
            RequestKeys::HAT_REQUESTED => $requested,
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas(User::tableName(), [
            'id'            => $user->getKey(),
            'hat_requested' => (int) $requested,
        ]);
    }

    /** @test */
    public function it_sends_email_to_support_when_hat_has_been_requested()
    {
        Event::fake([HatRequested::class]);
        $user = User::factory()->create();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->patch($route, [
            RequestKeys::HAT_REQUESTED => true,
        ]);

        $response->assertStatus(Response::HTTP_OK);
        Event::assertDispatched(HatRequested::class, function(HatRequested $event) use ($user) {
            $this->assertSame($user->getKey(), $event->user()->getKey());

            return true;
        });
    }

    /** @test
     * @dataProvider hatRequestedProvider
     */
    public function it_can_not_request_or_decline_a_hat_if_has_previously_done_so(bool $requested)
    {
        $user = User::factory()->create([
            'hat_requested' => $requested,
        ]);

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->patch($route, [
            RequestKeys::HAT_REQUESTED => !$requested,
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas(User::tableName(), [
            'id'            => $user->getKey(),
            'hat_requested' => (int) $requested,
        ]);
    }

    public function hatRequestedProvider(): array
    {
        return [
            [false],
            [true],
        ];
    }

    /** @test */
    public function it_dispatches_a_company_updated_event()
    {
        Event::fake(CompanyUpdated::class);

        $user        = User::factory()->create();
        $company     = Company::factory()->create();
        $companyUser = CompanyUser::factory()->usingUser($user)->usingCompany($company)->create();
        $country     = Country::build(CountryDataType::UNITED_STATES);
        /** @var State $state */
        $state = $country->getStates()->first();
        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->patch($route, [
            RequestKeys::COMPANY   => $company->getRouteKey(),
            RequestKeys::JOB_TITLE => 'Other',
        ]);

        $response->assertStatus(Response::HTTP_OK);

        Event::assertDispatched(CompanyUpdated::class, function(CompanyUpdated $event) use ($companyUser) {
            $this->assertEquals($companyUser->getKey(), $event->companyUser()->getKey());

            return true;
        });
    }

    /** @test */
    public function it_does_not_dispatches_a_company_updated_event_when_no_company_related_fields()
    {
        Event::fake(CompanyUpdated::class);

        $user    = User::factory()->create();
        $company = Company::factory()->create();
        CompanyUser::factory()->usingUser($user)->usingCompany($company)->create();
        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->patch($route, [
            RequestKeys::FIRST_NAME => 'John',
        ]);

        $response->assertStatus(Response::HTTP_OK);

        Event::assertNotDispatched(CompanyUpdated::class);
    }

    /** @test */
    public function it_should_verify_a_user_who_fill_all_required_data()
    {
        $user    = User::factory()->create();
        $company = Company::factory()->create();
        CompanyUser::factory()->usingUser($user)->usingCompany($company)->create();

        Phone::factory()->usingUser($user)->create();
        SupplierUser::factory()->usingUser($user)->createQuietly();

        $this->assertFalse($user->isVerified());

        $country = Country::build(CountryDataType::UNITED_STATES);
        /** @var State $state */
        $state = $country->getStates()->first();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->patch($route, [
            RequestKeys::FIRST_NAME  => 'John',
            RequestKeys::LAST_NAME   => 'Doe',
            RequestKeys::PUBLIC_NAME => 'JohnDoe',
            RequestKeys::ZIP_CODE    => '90210',
            RequestKeys::ADDRESS     => '1313 Evergreen St.',
            RequestKeys::ADDRESS_2   => 'Unit 2',
            RequestKeys::CITY        => 'Beverly Hills',
            RequestKeys::STATE       => $state->isoCode,
            RequestKeys::COUNTRY     => $country->code,
            RequestKeys::EXPERIENCE  => '6',
            RequestKeys::BIO         => 'Lorem ipsum',

            RequestKeys::JOB_TITLE              => CompanyDataType::getJobTitles(CompanyDataType::TYPE_CONTRACTOR)[0],
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => CompanyDataType::EQUIPMENT_TYPE_RESIDENTIAL,
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertTrue($user->fresh()->isVerified());
    }
}
