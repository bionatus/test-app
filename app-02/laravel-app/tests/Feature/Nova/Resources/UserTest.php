<?php

namespace Tests\Feature\Nova\Resources;

use App\Constants\MediaCollectionNames;
use App\Constants\RequestKeys;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Phone;
use App\Models\User;
use App\Types\CompanyDataType;
use App\Types\CountryDataType;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\Earth;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Storage;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

/** @see \App\Nova\Resources\User */
class UserTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = '/nova-api/' . \App\Nova\Resources\User::uriKey() . DIRECTORY_SEPARATOR;
    }

    /** @test */
    public function it_displays_a_list_of_users()
    {
        User::factory()->full()->count(40)->create();

        $response = $this->getJson($this->path);
        $response->assertStatus(Response::HTTP_OK);

        $users = User::all();
        $this->assertCount($response->json('total'), $users);

        $data = Collection::make($response->json('resources'));

        $firstPageUsers = $users->sortByDesc('id')->values()->take(count($data));

        $this->assertEquals($data->pluck('id.value'), $firstPageUsers->pluck('id'),
            'Resources are not in the correct order.');
    }

    /** @test */
    public function it_creates_a_user()
    {
        $user = User::factory()->full()->make();
        $data = Collection::make($user->toArray())->only([
            'email',
            'password',
            'first_name',
            'last_name',
        ]);

        $response = $this->postJson($this->path, $data->toArray());
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(User::tableName(), $data->except(['password'])->toArray());
    }

    /** @test */
    public function it_creates_a_user_and_a_company()
    {
        $user = User::factory()->full()->make();
        $data = Collection::make($user->toArray())->only([
            'email',
            'password',
            'first_name',
            'last_name',
        ]);

        $companyFields = Collection::make([
            RequestKeys::COMPANY_NAME     => 'name',
            RequestKeys::COMPANY_TYPE     => CompanyDataType::TYPE_PROPERTY_MANAGER_OWNER,
            RequestKeys::COMPANY_COUNTRY  => 'US',
            RequestKeys::COMPANY_STATE    => 'US-LA',
            RequestKeys::COMPANY_CITY     => 'city',
            RequestKeys::COMPANY_ZIP_CODE => '12345',
            RequestKeys::COMPANY_ADDRESS  => 'the new address',
        ]);

        $companyUserFields = Collection::make([
            RequestKeys::JOB_TITLE => 'Other',
        ]);

        $response = $this->postJson($this->path, $data->merge($companyFields)->merge($companyUserFields)->toArray());
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(User::tableName(), $data->except(['password'])->toArray());

        $this->assertDatabaseHas(Company::tableName(), [
            'name'     => $companyFields->get(RequestKeys::COMPANY_NAME),
            'type'     => $companyFields->get(RequestKeys::COMPANY_TYPE),
            'country'  => $companyFields->get(RequestKeys::COMPANY_COUNTRY),
            'state'    => $companyFields->get(RequestKeys::COMPANY_STATE),
            'city'     => $companyFields->get(RequestKeys::COMPANY_CITY),
            'zip_code' => $companyFields->get(RequestKeys::COMPANY_ZIP_CODE),
            'address'  => $companyFields->get(RequestKeys::COMPANY_ADDRESS),
        ]);

        $this->assertDatabaseHas(CompanyUser::tableName(), [
            'job_title' => $companyUserFields->get(RequestKeys::JOB_TITLE),
        ]);
    }

    /** @test * */
    public function it_creates_a_user_and_a_phone()
    {
        $user = User::factory()->full()->make();
        $data = Collection::make($user->toArray())->only([
            'email',
            'password',
            'first_name',
            'last_name',
        ]);

        $phoneFields = Collection::make([
            RequestKeys::COUNTRY_CODE => '12345',
            RequestKeys::PHONE        => '54321',
        ]);

        $response = $this->postJson($this->path, $data->merge($phoneFields)->toArray());

        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(User::tableName(), $data->except(['password'])->toArray());

        $this->assertDatabaseHas(Phone::tableName(), [
            'country_code' => $phoneFields->get(RequestKeys::COUNTRY_CODE),
            'number'       => $phoneFields->get(RequestKeys::PHONE),
        ]);
    }

    /** @test * */
    public function a_user_can_be_retrieved_with_correct_resource_elements()
    {
        Config::set('hubspot.form_url', $hubspotFormUrl = 'http://url?email=');

        $user = User::factory()->full()->create();
        Phone::factory()->usingUser($user)->create();
        $company  = Company::factory()->create(['type' => CompanyDataType::TYPE_CONTRACTOR]);
        $jobTitle = CompanyDataType::getJobTitles($company->type)[0];

        CompanyUser::factory()->usingUser($user)->usingCompany($company)->create([
            'job_title'      => $jobTitle,
            'equipment_type' => CompanyDataType::EQUIPMENT_TYPE_RESIDENTIAL,
        ]);

        $response = $this->getJson($this->path . $user->getKey());
        $response->assertStatus(Response::HTTP_OK);

        $countries = $this->getValidCountries()->toArray();

        $fields = [
            [
                'component' => 'id-field',
                'attribute' => 'id',
                'value'     => $user->getKey(),
                'name'      => 'ID',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'first_name',
                'value'     => $user->first_name,
                'name'      => 'First Name',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'last_name',
                'value'     => $user->last_name,
                'name'      => 'Last Name',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'email',
                'value'     => $user->email,
                'name'      => 'Email',
            ],
            [
                'component' => 'date',
                'attribute' => 'created_at',
                'value'     => $user->created_at->toDateString(),
                'name'      => 'Created At',
            ],
            [
                'component' => 'advanced-media-library-field',
                'attribute' => MediaCollectionNames::IMAGES,
                'name'      => 'Photo',
                'type'      => 'media',
            ],
            [
                'component' => 'boolean-field',
                'attribute' => 'verified',
                'name'      => 'Verified',
            ],
            [
                'component' => 'boolean-field',
                'attribute' => 'disabled_at',
                'name'      => 'Disabled',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'company_name',
                'value'     => $user->companyUser->company->name,
                'name'      => 'Name',
            ],
            [
                'component' => 'select-field',
                'attribute' => 'company_type',
                'value'     => $user->companyUser->company->type,
                'name'      => 'Type',
                'nullable'  => true,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'display_primary_equipment_type',
                'value'     => $user->companyUser->equipment_type,
                'name'      => 'What equipment do you primarily work on?',
            ],
            [
                'component' => 'nova-dependency-container',
                'fields'    => [
                    [
                        'attribute' => 'primary_equipment_type',
                        'component' => 'select-field',
                        'name'      => 'What equipment do you primarily work on?',
                        'value'     => $user->companyUser->equipment_type,
                    ],
                ],
            ],
            [
                'component' => 'nova-ajax-select',
                'attribute' => 'job_title',
                'value'     => $user->companyUser->job_title,
                'name'      => 'Job Title',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'display_job_title',
                'value'     => $user->companyUser->job_title,
                'name'      => 'Job Title',
            ],
            [
                'component' => 'select-field',
                'attribute' => 'company_country',
                'value'     => $user->companyUser->company->country,
                'name'      => 'Country',
                'nullable'  => true,
                'options'   => $countries,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'state',
                'value'     => $user->companyUser->company->state,
                'readonly'  => true,
                'name'      => 'State',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'company_city',
                'value'     => $user->companyUser->company->city,
                'name'      => 'City',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'company_address',
                'value'     => $user->companyUser->company->address,
                'name'      => 'Address',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'company_zip_code',
                'value'     => $user->companyUser->company->zip_code,
                'name'      => 'Zip Code',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'address',
                'value'     => $user->address,
                'name'      => 'Address',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'address_2',
                'value'     => $user->address_2,
                'name'      => 'Address 2',
            ],
            [
                'component' => 'select-field',
                'attribute' => 'country',
                'value'     => $user->country,
                'name'      => 'Country',
                'nullable'  => true,
                'options'   => $countries,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'state',
                'value'     => $user->state,
                'name'      => 'State',
                'readonly'  => true,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'zip',
                'value'     => $user->zip,
                'name'      => 'Zip Code',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'city',
                'value'     => $user->city,
                'name'      => 'City',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'country_code',
                'value'     => $user->phone()->first()->country_code,
                'name'      => 'Country Code',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'phone',
                'value'     => $user->phone()->first()->number,
                'name'      => 'Phone',
            ],
            [
                'component' => 'has-many-field',
                'attribute' => 'points',
                'value'     => null,
            ],
            [
                'component'                 => 'belongs-to-many-field',
                'resourceName'              => 'user-suppliers',
                'belongsToManyRelationship' => 'suppliers',
                'name'                      => 'Suppliers',
            ],
            [
                'component' => 'iframe',
                'panel'     => 'Supplier Map',
            ],
            [
                'component' => 'nova-button',
                'attribute' => 'hubspot_form',
                'name'      => 'Go To HubSpot Form',
                "link"      => ["href" => "{$hubspotFormUrl}{$user->email}", "target" => "_blank"],
            ],
        ];

        $this->assertCount(count($fields), $response->json('resource.fields'));

        $response->assertJson([
            'title'    => $user->name,
            'resource' => [
                'id'     => [
                    'value' => $user->getKey(),
                ],
                'fields' => $fields,
            ],
        ]);
    }

    /** @test */
    public function it_updates_a_user()
    {
        $user = User::factory()->create();

        $data           = User::factory()->make();
        $fieldsToUpdate = Collection::make($data->toArray())->except(['password', 'public_name', 'name'])->toArray();

        $response = $this->putJson($this->path . $user->getKey(), $fieldsToUpdate);
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas(User::tableName(), array_merge(['id' => $user->getKey()], $fieldsToUpdate));
    }

    /** @test */
    public function it_destroy_a_user()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson($this->path . '?resources[]=' . $user->getKey());

        $response->assertStatus(Response::HTTP_OK);

        $this->assertModelMissing($user);
    }

    /** @test */
    public function it_ignores_job_title_if_company_type_is_null()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_uploads_a_photo_in_media_collection()
    {
        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);

        $file = UploadedFile::fake()->image('avatar.jpeg');

        $user = User::factory()->create();

        $fieldsToUpdate = $this->validUserData()
            ->put('__media__', [MediaCollectionNames::IMAGES => [$file]])
            ->toArray();

        $response = $this->put($this->path . $user->getKey(), $fieldsToUpdate);
        $response->assertJsonMissingValidationErrors();

        $this->assertTrue($user->hasMedia(MediaCollectionNames::IMAGES));
        $this->assertCount(1, $user->getMedia(MediaCollectionNames::IMAGES));

        $media = $user->getFirstMedia(MediaCollectionNames::IMAGES);
        $this->assertSame($file->getClientOriginalName(), $media->file_name);
        $pathGenerator = PathGeneratorFactory::create($media);
        Storage::disk($diskName)->assertExists($pathGenerator->getPath($media) . $media->file_name);
    }

    /** @test */
    public function it_replaces_a_photo_in_media_collection()
    {
        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);

        $file = UploadedFile::fake()->image('avatar.jpeg');

        $user     = User::factory()->create();
        $oldImage = $user->addMedia($file)
            ->preservingOriginal()
            ->usingName('old_avatar.jpeg')
            ->toMediaCollection(MediaCollectionNames::IMAGES);

        $fieldsToUpdate = $this->validUserData()
            ->put('__media__', [MediaCollectionNames::IMAGES => [$file]])
            ->toArray();

        $response = $this->put($this->path . $user->getKey(), $fieldsToUpdate);
        $response->assertJsonMissingValidationErrors();

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

        $fieldsToUpdate = $this->validUserData()
            ->put('__media__', [MediaCollectionNames::IMAGES => [$file]])
            ->toArray();

        $response = $this->put($this->path . $user->getKey(), $fieldsToUpdate);
        $response->assertJsonMissingValidationErrors();

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

        $fieldsToUpdate = $this->validUserData()
            ->put('__media__', [MediaCollectionNames::IMAGES => [$file]])
            ->toArray();

        $response = $this->put($this->path . $user->getKey(), $fieldsToUpdate);
        $response->assertJsonMissingValidationErrors();

        $user->refresh();
        $this->assertNotNull($user->photo);
        Storage::disk('public')->assertExists($user->photo);
        Storage::disk('public')->assertMissing($oldPhotoName);
    }

    private function getValidCountries(): Collection
    {
        $geo = new Earth();

        return Collection::make($geo->getCountries()->useShortNames()->sortBy('name'))->filter(fn($country
        ) => in_array($country->code, CountryDataType::getAllowedCountries()))->map(fn(Country $country
        ) => ['label' => $country->getName(), 'value' => $country->code])->values();
    }

    private function validUserData(): Collection
    {
        return Collection::make([
            'first_name'                  => 'John',
            'last_name'                   => 'Doe',
            'email'                       => 'john@doe.com',
            'password'                    => 'password',
            'address'                     => '',
            'address_2'                   => '',
            'country'                     => '',
            'state'                       => '',
            'zip'                         => '',
            'city'                        => '',
            RequestKeys::COMPANY_NAME     => '',
            RequestKeys::COMPANY_TYPE     => '',
            RequestKeys::JOB_TITLE        => '',
            RequestKeys::COMPANY_COUNTRY  => '',
            RequestKeys::COMPANY_STATE    => '',
            RequestKeys::COMPANY_ZIP_CODE => '',
            RequestKeys::COMPANY_CITY     => '',
            RequestKeys::COUNTRY_CODE     => '',
            RequestKeys::COMPANY_ADDRESS  => '',
            RequestKeys::PHONE            => '',
        ]);
    }
}
