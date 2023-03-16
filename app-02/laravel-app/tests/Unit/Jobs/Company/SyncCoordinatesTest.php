<?php

namespace Tests\Unit\Jobs\Company;

use App\Jobs\Company\SyncCoordinates;
use App\Models\Company;
use App\Types\CountryDataType;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use ReflectionClass;
use Spatie\Geocoder\Geocoder;
use Tests\TestCase;

class SyncCoordinatesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SyncCoordinates::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new SyncCoordinates(new Company());

        $this->assertEquals('database', $job->connection);
    }

    /** @test */
    public function it_does_nothing_if_coordinates_were_already_set()
    {
        $company = Mockery::mock(Company::class);
        $company->shouldReceive('hasValidCoordinates')->withNoArgs()->once()->andReturnTrue();
        $company->shouldNotReceive('saveQuietly');

        $geoCoder = Mockery::mock(Geocoder::class);
        $geoCoder->shouldNotReceive('getCoordinatesForAddress');

        $job = new SyncCoordinates($company);
        $job->handle($geoCoder);
    }

    /** @test */
    public function it_does_nothing_if_zip_code_is_not_valid()
    {
        $company = Mockery::mock(Company::class);
        $company->shouldReceive('hasValidCoordinates')->withNoArgs()->once()->andReturnFalse();
        $company->shouldReceive('hasValidZipCode')->withNoArgs()->once()->andReturnFalse();
        $company->shouldNotReceive('saveQuietly');

        $geoCoder = Mockery::mock(Geocoder::class);
        $geoCoder->shouldNotReceive('getCoordinatesForAddress');

        $job = new SyncCoordinates($company);
        $job->handle($geoCoder);
    }

    /** @test */
    public function it_does_nothing_if_geo_coder_throws_exception()
    {
        $company = Mockery::mock(Company::class);
        $company->shouldReceive('hasValidCoordinates')->withNoArgs()->once()->andReturnFalse();
        $company->shouldReceive('hasValidZipCode')->withNoArgs()->once()->andReturnTrue();
        $company->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturn($zipCode = '11111');
        $company->shouldNotReceive('saveQuietly');

        $geoCoder = Mockery::mock(Geocoder::class);
        $geoCoder->shouldReceive('setCountry')->withArgs([CountryDataType::UNITED_STATES])->once();
        $geoCoder->shouldReceive('getCoordinatesForAddress')->withArgs([$zipCode])->once()->andThrow(Exception::class);

        $job = new SyncCoordinates($company);
        $job->handle($geoCoder);
    }

    /** @test */
    public function it_does_nothing_if_geo_coder_returns_empty_response()
    {
        $company = Mockery::mock(Company::class);
        $company->shouldReceive('hasValidCoordinates')->withNoArgs()->once()->andReturnFalse();
        $company->shouldReceive('hasValidZipCode')->withNoArgs()->once()->andReturnTrue();
        $company->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturn($zipCode = '11111');
        $company->shouldNotReceive('saveQuietly');

        $geoCode = [
            'accuracy' => Geocoder::RESULT_NOT_FOUND,
        ];

        $geoCoder = Mockery::mock(Geocoder::class);
        $geoCoder->shouldReceive('setCountry')->withArgs([CountryDataType::UNITED_STATES])->once();
        $geoCoder->shouldReceive('getCoordinatesForAddress')->withArgs([$zipCode])->once()->andReturn($geoCode);

        $job = new SyncCoordinates($company);
        $job->handle($geoCoder);
    }

    /** @test */
    public function it_fills_coordinates()
    {
        $company = Mockery::mock(Company::class);
        $company->shouldReceive('hasValidCoordinates')->withNoArgs()->once()->andReturnFalse();
        $company->shouldReceive('hasValidZipCode')->withNoArgs()->once()->andReturnTrue();
        $company->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturn($zipCode = '11111');
        $company->shouldReceive('setAttribute')->withArgs(['latitude', '0'])->once();
        $company->shouldReceive('setAttribute')->withArgs(['longitude', '0'])->once();
        $company->shouldReceive('saveQuietly')->withNoArgs()->once();

        $geoCode = [
            'lat'      => '0',
            'lng'      => '0',
            'accuracy' => 'rooftop',
        ];

        $geoCoder = Mockery::mock(Geocoder::class);
        $geoCoder->shouldReceive('setCountry')->withArgs([CountryDataType::UNITED_STATES])->once();
        $geoCoder->shouldReceive('getCoordinatesForAddress')->withArgs([$zipCode])->once()->andReturn($geoCode);

        $job = new SyncCoordinates($company);
        $job->handle($geoCoder);
    }
}
