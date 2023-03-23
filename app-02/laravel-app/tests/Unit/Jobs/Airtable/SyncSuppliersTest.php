<?php

namespace Tests\Unit\Jobs\Airtable;

use App;
use App\Constants\GeocoderAccuracyValues;
use App\Jobs\Airtable\SyncSuppliers;
use App\Lib\AirtableClient;
use App\Mail\Supplier\SyncReportEmail;
use App\Models\Supplier;
use App\Services\Hubspot\Hubspot;
use Config;
use Geocoder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mail;
use Mockery;
use ReflectionClass;
use Spatie\Geocoder\Exceptions\CouldNotGeocode;
use Tests\TestCase;

class SyncSuppliersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SyncSuppliers::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_fetch_a_table_from_airtable()
    {
        $client = Mockery::mock(AirtableClient::class);
        $client->shouldReceive('setEndpoint')->withArgs([null])->once();
        $job = new SyncSuppliers();
        $client->shouldReceive('prepare')->withArgs([[$job, 'parseResponse']])->once();
        /** @var AirtableClient $client */
        $job->handle($client);
    }

    /** @test */
    public function it_does_not_store_records_without_an_id()
    {
        $response = [[]];

        $job = new SyncSuppliers();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supplier::tableName(), 0);
    }

    /** @test */
    public function it_does_not_store_records_with_id_but_without_fields()
    {
        $response = [['id' => 'no fields']];

        $job = new SyncSuppliers();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supplier::tableName(), 0);
    }

    /** @test */
    public function it_does_not_store_records_without_an_airtable_id_field()
    {
        $response = [
            [
                'id'     => 'no airtable_id',
                'fields' => [],
            ],
        ];

        $job = new SyncSuppliers();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supplier::tableName(), 0);
    }

    /** @test */
    public function it_does_not_store_records_without_an_airtable_id()
    {
        $response = [
            [
                'id'     => 'no airtable_id',
                'fields' => [
                    'Unique #' => null,
                ],
            ],
        ];

        $job = new SyncSuppliers();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supplier::tableName(), 0);
    }

    /** @test */
    public function it_does_not_store_records_without_a_name_field()
    {
        $response = [
            [
                'id'     => 'no name',
                'fields' => [
                    'Unique #'     => 1,
                    'Name'         => null,
                    'Branch Email' => 'branch@email.com',
                ],
            ],
        ];

        $job = new SyncSuppliers();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supplier::tableName(), 0);
    }

    /** @test */
    public function it_does_not_store_records_without_an_email_field()
    {
        $response = [
            [
                'id'     => 'no email',
                'fields' => [
                    'Unique #'     => 1,
                    'Name'         => 'Valid name',
                    'Branch Email' => null,
                ],
            ],
        ];

        $job = new SyncSuppliers();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supplier::tableName(), 0);
    }

    /** @test */
    public function it_store_new_records()
    {
        $response = [
            [
                'id'     => 'valid',
                'fields' => [
                    'Unique #'     => 1,
                    'Name'         => 'New',
                    'Branch Email' => 'branch@email.com',
                ],
            ],
        ];

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->withAnyArgs()->once()->andReturnNull();
        App::bind(Hubspot::class, fn() => $hubspot);

        $job = new SyncSuppliers();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supplier::tableName(), 1);
        $this->assertDatabaseHas(Supplier::tableName(), [
            'airtable_id' => 1,
            'name'        => 'New',
        ]);
    }

    /** @test */
    public function it_updates_existing_records()
    {
        Supplier::factory()->createQuietly([
            'airtable_id' => $airtableId = 1,
            'name'        => 'Old',
        ]);
        Supplier::flushEventListeners();

        $response = [
            [
                'id'     => 'existing',
                'fields' => [
                    'Unique #'     => $airtableId,
                    'Branch Email' => 'branch@email.com',
                    'Name'         => $name = 'New',
                ],
            ],
        ];

        $job = new SyncSuppliers();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supplier::tableName(), 1);
        $this->assertDatabaseHas(Supplier::tableName(), [
            'airtable_id' => $airtableId,
            'name'        => $name,
        ]);
    }

    /** @test */
    public function it_ignores_duplicate_airtable_id_records()
    {
        $response = [
            [
                'id'     => 'existing',
                'fields' => [
                    'Unique #'     => $airtableId = 1,
                    'Branch Email' => 'branch@email.com',
                    'Name'         => $name = 'New',
                ],
            ],
            [
                'id'     => 'existing',
                'fields' => [
                    'Unique #'     => $airtableId,
                    'Branch Email' => 'anotherbranch@email.com',
                    'Name'         => 'Duplicated',
                ],
            ],
        ];

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->withAnyArgs()->once()->andReturnNull();
        App::bind(Hubspot::class, fn() => $hubspot);

        $job = new SyncSuppliers();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supplier::tableName(), 1);
        $this->assertDatabaseHas(Supplier::tableName(), [
            'airtable_id' => $airtableId,
            'name'        => $name,
        ]);
    }

    /** @test */
    public function it_ignores_duplicate_email_records()
    {
        $response = [
            [
                'id'     => 'existing',
                'fields' => [
                    'Unique #'     => $airtableId = 1,
                    'Branch Email' => $email = 'branch@email.com',
                    'Name'         => $name = 'New',
                ],
            ],
            [
                'id'     => 'existing',
                'fields' => [
                    'Unique #'     => 2,
                    'Branch Email' => $email,
                    'Name'         => 'Duplicated',
                ],
            ],
        ];

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->withAnyArgs()->once()->andReturnNull();
        App::bind(Hubspot::class, fn() => $hubspot);

        $job = new SyncSuppliers();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supplier::tableName(), 1);
        $this->assertDatabaseHas(Supplier::tableName(), [
            'airtable_id' => $airtableId,
            'email'       => $email,
            'name'        => $name,
        ]);
    }

    /** @test */
    public function it_does_not_store_records_with_an_airtable_id_of_more_than_255_characters()
    {
        $response = [
            [
                'id'     => 'Long unique #',
                'fields' => [
                    'Unique #'     => Str::random(256),
                    'Name'         => 'A Name',
                    'Branch Email' => 'branch@email.com',
                ],
            ],
        ];

        $job = new SyncSuppliers();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supplier::tableName(), 0);
    }

    /** @test */
    public function it_does_not_store_records_with_a_name_of_more_than_255_characters()
    {
        $response = [
            [
                'id'     => 'Long name',
                'fields' => [
                    'Unique #'     => 1,
                    'Name'         => Str::random(256),
                    'Branch Email' => 'branch@email.com',
                ],
            ],
        ];

        $job = new SyncSuppliers();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supplier::tableName(), 0);
    }

    /** @test */
    public function it_does_not_store_records_with_an_invalid_email()
    {
        $response = [
            [
                'id'     => 'Long name',
                'fields' => [
                    'Unique #'     => 1,
                    'Name'         => 'A Name',
                    'Branch Email' => 'invalid',
                ],
            ],
        ];

        $job = new SyncSuppliers();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supplier::tableName(), 0);
    }

    /** @test */
    public function it_does_not_update_the_latitude_and_longitude_if_the_update_coordinates_option_is_not_set()
    {
        Geocoder::shouldReceive('getCoordinatesForAddress')->never();

        $response = [
            [
                'id'     => 'valid',
                'fields' => [
                    'Unique #'     => 1,
                    'Name'         => 'New',
                    'Branch Email' => 'branch@email.com',
                    'Lat'          => '0',
                    'Lon'          => '0',
                ],
            ],
        ];

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->withAnyArgs()->once()->andReturnNull();
        App::bind(Hubspot::class, fn() => $hubspot);

        $job = new SyncSuppliers();
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supplier::tableName(), 1);
        $this->assertDatabaseHas(Supplier::tableName(), [
            'airtable_id' => 1,
            'name'        => 'New',
            'latitude'    => '0',
            'longitude'   => '0',
        ]);
    }

    /** @test */
    public function it_does_not_update_the_latitude_and_longitude_if_it_could_not_get_a_geocode_for_supplier_address()
    {
        Geocoder::shouldReceive('getCoordinatesForAddress')->once()->andThrow(CouldNotGeocode::couldNotConnect());

        $response = [
            [
                'id'     => 'valid',
                'fields' => [
                    'Unique #'     => 1,
                    'Name'         => 'New',
                    'Branch Email' => 'branch@email.com',
                    'Lat'          => '0',
                    'Lon'          => '0',
                ],
            ],
        ];

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->withAnyArgs()->once()->andReturnNull();
        App::bind(Hubspot::class, fn() => $hubspot);

        $job = new SyncSuppliers(true);
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supplier::tableName(), 1);
        $this->assertDatabaseHas(Supplier::tableName(), [
            'airtable_id' => 1,
            'name'        => 'New',
            'latitude'    => '0',
            'longitude'   => '0',
        ]);
    }

    /** @test */
    public function it_updates_the_latitude_and_longitude_for_supplier_address()
    {
        Geocoder::shouldReceive('getCoordinatesForAddress')->once()->andReturn([
            'formatted_address' => 'a valid address',
            'lat'               => '45',
            'lng'               => '90',
            'accuracy'          => GeocoderAccuracyValues::ROOFTOP,
        ]);

        $response = [
            [
                'id'     => 'valid',
                'fields' => [
                    'Unique #'     => 1,
                    'Name'         => 'New',
                    'Branch Email' => 'branch@email.com',
                    'Address'      => 'Infinite Loop 1',
                    'City'         => 'Cupertino',
                    'State'        => 'CA',
                    'Postal Code'  => '95014',
                    'Country'      => 'USA',
                    'Lat'          => '0',
                    'Lon'          => '0',
                ],
            ],
        ];

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->withAnyArgs()->once()->andReturnNull();
        App::bind(Hubspot::class, fn() => $hubspot);

        $job = new SyncSuppliers(true);
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supplier::tableName(), 1);
        $this->assertDatabaseHas(Supplier::tableName(), [
            'airtable_id' => 1,
            'name'        => 'New',
            'latitude'    => '45',
            'longitude'   => '90',
        ]);
    }

    /** @test */
    public function it_updates_the_latitude_and_longitude_for_suppliers_with_incomplete_address()
    {
        Geocoder::shouldReceive('getCoordinatesForAddress')->once()->andReturn([
            'formatted_address' => 'a valid address',
            'lat'               => '45',
            'lng'               => '90',
            'accuracy'          => GeocoderAccuracyValues::ROOFTOP,
        ]);

        $response = [
            [
                'id'     => 'valid',
                'fields' => [
                    'Unique #'     => 1,
                    'Name'         => 'New',
                    'Branch Email' => 'branch@email.com',
                    'Address'      => 'Infinite Loop 1',
                    'City'         => 'Cupertino',
                    'Lat'          => '0',
                    'Lon'          => '0',
                ],
            ],
        ];

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->withAnyArgs()->once()->andReturnNull();
        App::bind(Hubspot::class, fn() => $hubspot);

        $job = new SyncSuppliers(true);
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supplier::tableName(), 1);
        $this->assertDatabaseHas(Supplier::tableName(), [
            'airtable_id' => 1,
            'name'        => 'New',
            'latitude'    => '45',
            'longitude'   => '90',
        ]);
    }

    /**
     * @test
     * @dataProvider accuracyProvider
     */
    public function it_does_not_update_the_coordinates_when_the_address_is_not_accurate_enough($invalidAccuracy)
    {
        Geocoder::shouldReceive('getCoordinatesForAddress')->once()->andReturn([
            'formatted_address' => 'a valid address',
            'lat'               => '90',
            'lng'               => '90',
            'accuracy'          => $invalidAccuracy,
        ]);

        $response = [
            [
                'id'     => 'valid',
                'fields' => [
                    'Unique #'     => 1,
                    'Name'         => 'New',
                    'Branch Email' => 'branch@email.com',
                    'Lat'          => '0',
                    'Lon'          => '0',
                ],
            ],
        ];

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->withAnyArgs()->once()->andReturnNull();
        App::bind(Hubspot::class, fn() => $hubspot);

        $job = new SyncSuppliers(true);
        $job->parseResponse($response);

        $this->assertDatabaseCount(Supplier::tableName(), 1);
        $this->assertDatabaseHas(Supplier::tableName(), [
            'airtable_id' => 1,
            'name'        => 'New',
            'latitude'    => '0',
            'longitude'   => '0',
        ]);
    }

    public function accuracyProvider(): array
    {
        return [
            [GeocoderAccuracyValues::GEOMETRIC_CENTER],
            [GeocoderAccuracyValues::APPROXIMATE],
        ];
    }

    /** @test */
    public function it_removes_records_that_are_not_in_results()
    {
        $supplier = Supplier::factory()->createQuietly([
            'airtable_id' => 1,
            'name'        => 'To be deleted',
        ]);

        $client = Mockery::mock(AirtableClient::class);
        $client->shouldReceive('setEndpoint')->withArgs([null])->once();

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->withAnyArgs()->once()->andReturnNull();
        App::bind(Hubspot::class, fn() => $hubspot);

        $job = new SyncSuppliers();
        $job->parseResponse([
            [
                'id'     => 'New',
                'fields' => [
                    'Unique #'     => 2,
                    'Branch Email' => 'branch@email.com',
                    'Name'         => 'To be created',
                ],
            ],
        ]);
        $client->shouldReceive('prepare')->withArgs([[$job, 'parseResponse']])->once();
        /** @var AirtableClient $client */
        $job->handle($client);

        $this->assertModelMissing($supplier);
        $this->assertDatabaseHas(Supplier::tableName(), ['airtable_id' => 2]);
    }

    /** @test */
    public function it_sends_an_report_email()
    {
        Config::set('mail.reports.sync', 'reports@email.com');
        Mail::fake();
        Supplier::factory()->createQuietly([
            'airtable_id' => 1,
            'name'        => 'To be deleted',
        ]);

        $client = Mockery::mock(AirtableClient::class);
        $client->shouldReceive('setEndpoint')->withArgs([null])->once();

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->withAnyArgs()->once()->andReturnNull();
        App::bind(Hubspot::class, fn() => $hubspot);

        $job = new SyncSuppliers();
        $job->parseResponse([
            [
                'id'     => 'New',
                'fields' => [
                    'Unique #'     => 2,
                    'Branch Email' => 'branch@email.com',
                    'Name'         => 'To be created',
                ],
            ],
        ]);
        $client->shouldReceive('prepare')->withArgs([[$job, 'parseResponse']])->once();
        /** @var AirtableClient $client */
        $job->handle($client);

        Mail::assertQueued(SyncReportEmail::class);
    }
}
