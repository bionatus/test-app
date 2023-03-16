<?php

namespace Tests\Unit\Actions\Models\Company;

use App\Actions\Models\Company\UpdateCoordinates;
use App\Jobs\Company\SyncCoordinates;
use App\Models\Company;
use App\Types\CountryDataType;
use Bus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use ReflectionProperty;
use Tests\TestCase;

class UpdateCoordinatesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_reset_coordinates_if_has_no_valid_zip_code_country_combination()
    {
        $company = Mockery::mock(Company::class);

        $company->shouldReceive('hasValidZipCode')->withNoArgs()->once()->andReturnFalse();
        $company->shouldReceive('setAttribute')->withArgs(['latitude', null])->once();
        $company->shouldReceive('setAttribute')->withArgs(['longitude', null])->once();
        $company->shouldReceive('saveQuietly');

        $action = new UpdateCoordinates($company);
        $action->execute();
    }

    /** @test */
    public function it_fills_coordinates_from_db_if_same_zip_code_with_coordinates_already_exist()
    {
        $zipCode   = '11111';
        $latitude  = '0';
        $longitude = '0';

        $company = Company::factory()->createQuietly([
            'uuid'      => '123456-123456-4321',
            'country'   => CountryDataType::UNITED_STATES,
            'zip_code'  => $zipCode,
            'latitude'  => '1',
            'longitude' => '1',
        ]);

        Company::factory()->make([
            'uuid'      => '123456-123456-1234',
            'country'   => CountryDataType::UNITED_STATES,
            'zip_code'  => $zipCode,
            'latitude'  => $latitude,
            'longitude' => $longitude,
        ])->saveQuietly();

        $action = new UpdateCoordinates($company);
        $action->execute();
        $company->refresh();

        $this->assertSame($latitude, $company->latitude);
        $this->assertSame($longitude, $company->longitude);
    }

    /** @test */
    public function it_queues_job_to_fetch_coordinates_from_third_party_api_and_reset_current_coordinates()
    {
        Bus::fake(SyncCoordinates::class);

        $company = Company::factory()->make([
            'country'   => CountryDataType::UNITED_STATES,
            'zip_code'  => '11111',
            'latitude'  => 1,
            'longitude' => 1,
        ]);

        $action = new UpdateCoordinates($company);
        $action->execute();

        Bus::assertDispatched(SyncCoordinates::class, function(SyncCoordinates $job) use ($company) {
            $property = new ReflectionProperty($job, 'company');
            $property->setAccessible(true);
            /** @var Company $jobCompany */
            $jobCompany = $property->getValue($job);

            $this->assertSame($company, $jobCompany);

            $this->assertNull($jobCompany->latitude);
            $this->assertNull($jobCompany->longitude);

            return true;
        });
    }
}
