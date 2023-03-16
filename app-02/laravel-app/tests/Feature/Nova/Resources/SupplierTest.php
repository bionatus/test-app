<?php

namespace Tests\Feature\Nova\Resources;

use App;
use App\Constants\MediaCollectionNames;
use App\Constants\Timezones;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\SupplierCompany;
use App\Services\Hubspot\Hubspot;
use App\Types\CountryDataType;
use Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\Earth;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

/** @see \App\Nova\Resources\Supplier */
class SupplierTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = '/nova-api/' . App\Nova\Resources\Supplier::uriKey() . DIRECTORY_SEPARATOR;
    }

    /** @test */
    public function it_displays_a_list_of_suppliers()
    {
        $suppliers = Supplier::factory()->full()->count(40)->createQuietly();

        $response = $this->getJson($this->path);
        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount($response->json('total'), $suppliers);

        $data = Collection::make($response->json('resources'));

        $firstPageStores = $suppliers->sortByDesc('id')->values()->take(count($data));

        $this->assertEquals($data->pluck('id.value'), $firstPageStores->pluck('id'),
            'Resources are not in the correct order.');
    }

    /** @test */
    public function it_creates_a_supplier_with_an_staff_owner()
    {
        $supplierFields = $this->getSupplierFields();

        $requestFields = $this->processFieldsForRequest($supplierFields);
        $response      = $this->postJson($this->path, $requestFields->toArray());

        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(Supplier::tableName(), $this->fieldsForDatabaseCheck($supplierFields)->toArray());

        $this->assertDatabaseHas(Staff::tableName(), [
            'type'  => Staff::TYPE_OWNER,
            'email' => $supplierFields->get('email'),
        ]);

        $staff = Staff::first();
        $this->assertTrue(Hash::check($requestFields->get('password'), $staff->password));
    }

    /** @test */
    public function it_creates_a_manager_for_the_supplier()
    {
        $supplierFields = $this->getSupplierFields();

        $requestFields = $this->processFieldsForRequest($supplierFields);
        $response      = $this->postJson($this->path, $requestFields->merge([
            Staff::TYPE_MANAGER . '_name'  => $name = 'Manager',
            Staff::TYPE_MANAGER . '_phone' => $phone = 123456,
            Staff::TYPE_MANAGER . '_email' => $email = 'manager@store.com',
        ])->toArray());
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);

        $supplier = Supplier::first();

        $this->assertDatabaseHas(Staff::tableName(), [
            'supplier_id' => $supplier->getKey(),
            'type'        => Staff::TYPE_MANAGER,
            'name'        => $name,
            'phone'       => $phone,
            'email'       => $email,
        ]);
    }

    /** @test */
    public function it_creates_an_accountant_for_the_supplier()
    {
        $supplierFields = $this->getSupplierFields();

        $requestFields = $this->processFieldsForRequest($supplierFields);
        $response      = $this->postJson($this->path, $requestFields->merge([
            Staff::TYPE_ACCOUNTANT . '_name'  => $name = 'Accountant',
            Staff::TYPE_ACCOUNTANT . '_phone' => $phone = 123456,
            Staff::TYPE_ACCOUNTANT . '_email' => $email = 'accountant@store.com',
        ])->toArray());
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);

        $supplier = Supplier::first();

        $this->assertDatabaseHas(Staff::tableName(), [
            'supplier_id' => $supplier->getKey(),
            'type'        => Staff::TYPE_ACCOUNTANT,
            'name'        => $name,
            'phone'       => $phone,
            'email'       => $email,
        ]);
    }

    /** @test * */
    public function a_supplier_can_be_retrieved_with_correct_resource_elements()
    {
        $supplierCompany = SupplierCompany::factory()->create();
        $supplier        = Supplier::factory()->full()->usingSupplierCompany($supplierCompany)->createQuietly();
        $manager         = Staff::factory()->manager()->usingSupplier($supplier)->create();
        $accountant      = Staff::factory()->accountant()->usingSupplier($supplier)->create();

        $response = $this->getJson($this->path . $supplier->getKey());

        $response->assertStatus(Response::HTTP_OK);

        $countries = $this->getValidCountries()->toArray();

        $fields = [
            [
                'component' => 'id-field',
                'attribute' => 'id',
                'value'     => $supplier->getKey(),
            ],
            [
                'component' => 'text-field',
                'attribute' => 'airtable_id',
                'value'     => $supplier->airtable_id,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'name',
                'value'     => $supplier->name,
            ],
            [
                'component' => 'advanced-media-library-field',
                'attribute' => MediaCollectionNames::LOGO,
                'name'      => 'Logo',
                'type'      => 'media',
            ],
            [
                'component' => 'advanced-media-library-field',
                'attribute' => MediaCollectionNames::IMAGES,
                'name'      => 'Image',
                'type'      => 'media',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'branch',
                'value'     => $supplier->branch,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'address',
                'value'     => $supplier->address,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'address_2',
                'value'     => $supplier->address_2,
            ],
            [
                'component' => 'select-field',
                'attribute' => 'timezone',
                'value'     => $supplier->timezone,
            ],
            [
                'component' => 'select-field',
                'attribute' => 'country',
                'value'     => $supplier->country,
                'options'   => $countries,
            ],
            [
                'component' => 'nova-ajax-select',
                'attribute' => 'state',
                'value'     => $supplier->state,
                'name'      => 'State',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'display_state',
                'value'     => $supplier->state,
                'name'      => 'State',
                'readonly'  => true,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'city',
                'value'     => $supplier->city,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'zip_code',
                'value'     => $supplier->zip_code,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'email',
                'value'     => $supplier->email,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'phone',
                'value'     => $supplier->phone,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'prokeep_phone',
                'value'     => $supplier->prokeep_phone,
            ],
            [
                'component' => 'boolean-field',
                'attribute' => 'offers_delivery',
                'value'     => $supplier->offers_delivery,
            ],
            [
                'component' => 'boolean-field',
                'attribute' => 'published_at',
                'value'     => !!$supplier->published_at,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'take_rate',
                'value'     => round($supplier->take_rate / 100, 2),
                'step'      => 0.01,
            ],
            [
                'component' => 'date',
                'attribute' => 'take_rate_until',
                'value'     => $supplier->take_rate_until->toDateString(),
            ],
            [
                'component' => 'text-field',
                'attribute' => 'terms',
                'value'     => $supplier->terms,
            ],
            [
                'component'   => 'belongs-to-field',
                'attribute'   => 'supplierCompany',
                'searchable'  => true,
                'belongsToId' => $supplierCompany->getKey(),
                'value'       => $supplierCompany->name,
            ],
            [
                'component' => 'textarea-field',
                'attribute' => 'about',
                'value'     => $supplier->about,
            ],
            [
                'component' => 'has-many-field',
                'attribute' => 'supplierHours',
                'value'     => null,
            ],
            [
                'component' => 'has-many-field',
                'attribute' => 'counters',
                'value'     => null,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'contact_phone',
                'value'     => $supplier->contact_phone,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'contact_email',
                'value'     => $supplier->contact_email,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'contact_secondary_email',
                'value'     => $supplier->contact_secondary_email,
            ],
            [
                'component' => 'text-field',
                'attribute' => Staff::TYPE_MANAGER . '_name',
                'value'     => $manager->name,
            ],
            [
                'component' => 'text-field',
                'attribute' => Staff::TYPE_MANAGER . '_phone',
                'value'     => $manager->phone,
            ],
            [
                'component' => 'text-field',
                'attribute' => Staff::TYPE_MANAGER . '_email',
                'value'     => $manager->email,
            ],
            [
                'component' => 'text-field',
                'attribute' => Staff::TYPE_ACCOUNTANT . '_name',
                'value'     => $accountant->name,
            ],
            [
                'component' => 'text-field',
                'attribute' => Staff::TYPE_ACCOUNTANT . '_phone',
                'value'     => $accountant->phone,
            ],
            [
                'component' => 'text-field',
                'attribute' => Staff::TYPE_ACCOUNTANT . '_email',
                'value'     => $accountant->email,
            ],
            [
                'component' => 'nova-map-marker-field',
                'attribute' => 'location',
                'value'     => json_encode([
                    "latitude_field"  => 'latitude',
                    'longitude_field' => 'longitude',
                    'latitude'        => $supplier->latitude ?: 0,
                    'longitude'       => $supplier->longitude ?: 0,
                ]),
            ],
        ];
        $this->assertCount(count($fields), $response->json('resource.fields'));

        $response->assertJson([
            'title'    => $supplier->name,
            'resource' => [
                'id'     => [
                    'value' => $supplier->getKey(),
                ],
                'fields' => $fields,
            ],
        ]);
    }

    /** @test */
    public function it_updates_a_supplier_and_the_staff_owner_email()
    {
        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->withAnyArgs()->twice()->andReturnNull();
        App::bind(Hubspot::class, fn() => $hubspot);

        $supplier = Supplier::factory()->create();
        $staff    = Staff::factory()->usingSupplier($supplier)->create([
            'password' => $oldPassword = 'aHashedPassword',
            'email'    => 'old@email.com',
        ]);

        $fieldsToUpdate = Collection::make([
            'name'                    => 'A name',
            'branch'                  => 123,
            'address'                 => 'Address',
            'address_2'               => 'Address 2',
            'timezone'                => Timezones::AMERICA_ADAK,
            'country'                 => 'US',
            'city'                    => 'A city',
            'state'                   => 'A State',
            'zip_code'                => 10000,
            'email'                   => $email = 'email@store.com',
            'phone'                   => 123456,
            'prokeep_phone'           => 234567,
            'offers_delivery'         => false,
            'take_rate'               => 300,
            'take_rate_until'         => '2023-01-12',
            'terms'                   => '2.5%/10 Net 90',
            'contact_phone'           => 345678,
            'contact_email'           => 'contact@store.com',
            'contact_secondary_email' => 4567890,
        ]);
        $requestFields  = $this->processFieldsForRequest($fieldsToUpdate)->except(['password']);

        $response = $this->putJson($this->path . $supplier->getKey(), $requestFields->toArray());
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas(Supplier::tableName(),
            $this->fieldsForDatabaseCheck($fieldsToUpdate)->put('id', $supplier->getKey())->toArray());

        $this->assertDatabaseHas(Staff::tableName(), [
            'id'          => $staff->getKey(),
            'supplier_id' => $supplier->getKey(),
            'type'        => Staff::TYPE_OWNER,
            'email'       => $email,
            'password'    => $oldPassword,
        ]);
    }

    /** @test */
    public function it_updates_the_staff_owner_password()
    {
        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->withAnyArgs()->twice()->andReturnNull();
        App::bind(Hubspot::class, fn() => $hubspot);

        $supplier = Supplier::factory()->create();
        $staff    = Staff::factory()->usingSupplier($supplier)->create([
            'password' => 'aHashedPassword',
            'email'    => 'old@email.com',
        ]);

        $fieldsToUpdate = Collection::make([
            'name'                    => 'A name',
            'branch'                  => 123,
            'address'                 => 'Address',
            'address_2'               => 'Address 2',
            'country'                 => 'US',
            'city'                    => 'A city',
            'state'                   => 'A State',
            'zip_code'                => 10000,
            'email'                   => $email = 'email@store.com',
            'phone'                   => 123456,
            'prokeep_phone'           => 234567,
            'offers_delivery'         => false,
            'take_rate'               => 300,
            'take_rate_until'         => '2023-01-12',
            'terms'                   => '2.5%/10 Net 90',
            'contact_phone'           => 345678,
            'contact_email'           => 'contact@store.com',
            'contact_secondary_email' => 4567890,
        ]);
        $requestFields  = $this->processFieldsForRequest($fieldsToUpdate);

        $response = $this->putJson($this->path . $supplier->getKey(),
            $requestFields->put('password', $newPassword = 'ABrandNewPassword')->toArray());
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas(Supplier::tableName(), $this->fieldsForDatabaseCheck($fieldsToUpdate)
            ->put('id', $supplier->getKey())
            ->except(['password'])
            ->toArray());

        $this->assertDatabaseHas(Staff::tableName(), [
            'id'          => $staff->getKey(),
            'supplier_id' => $supplier->getKey(),
            'type'        => Staff::TYPE_OWNER,
            'email'       => $email,
        ]);

        $staff = Staff::first();
        $this->assertTrue(Hash::check($newPassword, $staff->password));
    }

    /** @test */
    public function it_updates_the_manager_of_the_supplier()
    {
        $supplier = Supplier::factory()->createQuietly();
        $manager  = Staff::factory()->manager()->usingSupplier($supplier)->create();

        $supplierFields = $this->getSupplierFields();
        $requestFields  = $this->processFieldsForRequest($supplierFields);

        $response = $this->putJson($this->path . $supplier->getKey(), $requestFields->merge([
            Staff::TYPE_MANAGER . '_name'  => $name = 'Manager',
            Staff::TYPE_MANAGER . '_phone' => $phone = 123456,
            Staff::TYPE_MANAGER . '_email' => $email = 'manager@store.com',
        ])->toArray());
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas(Staff::tableName(), [
            'id'          => $manager->getKey(),
            'supplier_id' => $supplier->getKey(),
            'name'        => $name,
            'phone'       => $phone,
            'email'       => $email,
        ]);
    }

    /** @test */
    public function it_updates_the_accountant_of_the_supplier()
    {
        $supplier   = Supplier::factory()->createQuietly();
        $accountant = Staff::factory()->accountant()->usingSupplier($supplier)->create();

        $supplierFields = $this->getSupplierFields();
        $requestFields  = $this->processFieldsForRequest($supplierFields);

        $response = $this->putJson($this->path . $supplier->getKey(), $requestFields->merge([
            Staff::TYPE_ACCOUNTANT . '_name'  => $name = 'Accountant',
            Staff::TYPE_ACCOUNTANT . '_phone' => $phone = 123456,
            Staff::TYPE_ACCOUNTANT . '_email' => $email = 'accountant@store.com',
        ])->toArray());
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas(Staff::tableName(), [
            'id'          => $accountant->getKey(),
            'supplier_id' => $supplier->getKey(),
            'name'        => $name,
            'phone'       => $phone,
            'email'       => $email,
        ]);
    }

    /** @test */
    public function it_does_not_destroy_a_supplier()
    {
        $supplier = Supplier::factory()->createQuietly();

        $response = $this->deleteJson($this->path . '?resources[]=' . $supplier->getKey());

        $response->assertStatus(Response::HTTP_OK);

        $this->assertModelExists($supplier);
    }

    private function getSupplierFields(): Collection
    {
        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->withAnyArgs()->once()->andReturnNull();
        App::bind(Hubspot::class, fn() => $hubspot);

        $supplier = Supplier::factory()->full()->make();

        return Collection::make($supplier->toArray())->only([
            'name',
            'branch',
            'address',
            'address_2',
            'country',
            'city',
            'state',
            'zip_code',
            'email',
            'phone',
            'prokeep_phone',
            'offers_delivery',
            'published_at',
            'take_rate',
            'take_rate_until',
            'terms',
            'about',
            'contact_phone',
            'contact_email',
            'contact_secondary_email',
        ]);
    }

    private function processFieldsForRequest(Collection $fields): Collection
    {
        return $fields->merge([
            'password'  => 'password123',
            'take_rate' => round($fields->get('take_rate') / 100, 2),
        ]);
    }

    private function fieldsForDatabaseCheck(Collection $fields): Collection
    {
        $takeRateUntil = Carbon::make($fields->get('take_rate_until'))->toDateTimeString();
        $fields->put('take_rate_until', $takeRateUntil);

        return $fields;
    }

    private function getValidCountries(): Collection
    {
        $geo = new Earth();

        return Collection::make($geo->getCountries()->useShortNames()->sortBy('name'))->filter(fn($country
        ) => in_array($country->code, CountryDataType::getAllowedCountries()))->map(fn(Country $country
        ) => ['label' => $country->getName(), 'value' => $country->code])->values();
    }
}
