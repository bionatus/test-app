<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\HasState;
use App\Models\HasUuid;
use App\Types\CountryDataType;
use Illuminate\Support\Str;
use ReflectionException;

class CompanyTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Company::tableName(), [
            'id',
            'uuid',
            'name',
            'type',
            'country',
            'state',
            'city',
            'address',
            'zip_code',
            'latitude',
            'longitude',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_traits()
    {
        $this->assertUseTrait(Company::class, HasState::class);
        $this->assertUseTrait(Company::class, HasUuid::class);
    }

    /** @test */
    public function it_uses_uuid()
    {
        $model = Company::factory()->create(['uuid' => Str::uuid()->toString()]);

        $this->assertEquals($model->uuid, $model->getRouteKey());
    }

    /** @test */
    public function it_fills_uuid_on_creation()
    {
        $model = Company::factory()->make(['uuid' => null]);
        $model->save();

        $this->assertNotNull($model->uuid);
    }

    /** @test */
    public function it_knows_if_has_valid_coordinates_set()
    {
        $valid                = Company::factory()->make([
            'latitude'  => '0',
            'longitude' => '0',
        ]);
        $noLatitude           = Company::factory()->make([
            'latitude'  => null,
            'longitude' => '0',
        ]);
        $noLongitude          = Company::factory()->make([
            'latitude'  => '0',
            'longitude' => null,
        ]);
        $noCoordinates        = Company::factory()->make([
            'latitude'  => null,
            'longitude' => null,
        ]);
        $latitudeOutOfBounds  = Company::factory()->make([
            'latitude'  => '91',
            'longitude' => '0',
        ]);
        $longitudeOutOfBounds = Company::factory()->make([
            'latitude'  => '0',
            'longitude' => '181',
        ]);

        $this->assertTrue($valid->hasValidCoordinates(), 'Coordinates are invalid');
        $this->assertFalse($noLongitude->hasValidCoordinates());
        $this->assertFalse($noLatitude->hasValidCoordinates());
        $this->assertFalse($noCoordinates->hasValidCoordinates());
        $this->assertFalse($latitudeOutOfBounds->hasValidCoordinates());
        $this->assertFalse($longitudeOutOfBounds->hasValidCoordinates());
    }

    /** @test */
    public function it_knows_if_has_valid_zip_code()
    {
        $nonUSInvalidZipCode = Company::factory()->create([
            'country'  => 'MX',
            'zip_code' => '123',
        ]);
        $nonUSValidZipCode   = Company::factory()->create([
            'country'  => 'MX',
            'zip_code' => '90210',
        ]);

        $usInvalidZipCode = Company::factory()->create([
            'country'  => CountryDataType::UNITED_STATES,
            'zip_code' => '123',
        ]);
        $usValidZipCode   = Company::factory()->create([
            'country'  => CountryDataType::UNITED_STATES,
            'zip_code' => '90210',
        ]);

        $this->assertFalse($nonUSInvalidZipCode->hasValidZipCode());
        $this->assertFalse($nonUSValidZipCode->hasValidZipCode());
        $this->assertFalse($usInvalidZipCode->hasValidZipCode());
        $this->assertTrue($usValidZipCode->hasValidZipCode());
    }
}
