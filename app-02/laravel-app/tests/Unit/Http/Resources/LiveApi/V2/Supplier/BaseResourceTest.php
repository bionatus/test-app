<?php

namespace Tests\Unit\Http\Resources\LiveApi\V2\Supplier;

use App;
use App\Actions\Models\Setting\GetSupplierSetting;
use App\Constants\MediaCollectionNames;
use App\Constants\MediaConversionNames;
use App\Http\Resources\LiveApi\V2\Supplier\BaseResource;
use App\Http\Resources\LiveApi\V2\Supplier\BrandCollection;
use App\Http\Resources\LiveApi\V2\Supplier\CounterStaffResource;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Models\SupplierHourResource;
use App\Http\Resources\Types\CountryResource;
use App\Http\Resources\Types\StateResource;
use App\Models\Media;
use App\Models\Staff;
use App\Models\Supplier;
use App\Types\CountryDataType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\State;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $id       = '123456-654321';
        $name     = 'Acme Inc.';
        $branch   = 1;
        $address  = '123 St.';
        $address2 = 'Apt. D.';
        $country  = Country::build(CountryDataType::UNITED_STATES);
        /** @var State $state */
        $state           = $country->getStates()->first();
        $countryResource = new CountryResource($country);
        $stateResource   = new StateResource($state);
        $city            = 'Warsaw';
        $zipCode         = '12346';
        $email           = 'acme@inc.com';
        $phone           = '123456789';
        $about           = 'about';
        $now             = Carbon::now();
        $delivery        = true;
        $take_rate       = Supplier::DEFAULT_TAKE_RATE;
        $take_rate_until = Carbon::create(Supplier::DEFAULT_YEAR, Supplier::DEFAULT_MONTH, Supplier::DEFAULT_DAY);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name);
        $supplier->shouldReceive('getAttribute')->withArgs(['branch'])->once()->andReturn($branch);
        $supplier->shouldReceive('getAttribute')->withArgs(['email'])->once()->andReturn($email);
        $supplier->shouldReceive('getAttribute')->withArgs(['phone'])->once()->andReturn($phone);
        $supplier->shouldReceive('getAttribute')->withArgs(['prokeep_phone'])->once()->andReturn($phone);
        $supplier->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturn($address);
        $supplier->shouldReceive('getAttribute')->withArgs(['address_2'])->once()->andReturn($address2);
        $supplier->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturn($zipCode);
        $supplier->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturn($city);
        $supplier->shouldReceive('getAttribute')->withArgs(['state'])->once()->andReturn($state->isoCode);
        $supplier->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturn($country->isoCode);
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['timezone'])
            ->once()
            ->andReturn($timezone = 'America/New_York');
        $supplier->shouldReceive('getAttribute')->withArgs(['about'])->once()->andReturn($about);
        $supplier->shouldReceive('getAttribute')->withArgs(['supplierHours'])->once()->andReturn(new Collection());
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['contact_phone'])
            ->once()
            ->andReturn($contactPhone = '1234567');
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['contact_email'])
            ->once()
            ->andReturn($contactEmail = 'contact@example.com');
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['contact_secondary_email'])
            ->once()
            ->andReturn($contactSecondaryEmail = 'contact2@example.com');
        $supplier->shouldReceive('getAttribute')->withArgs(['verified_at'])->once()->andReturn($now);
        $supplier->shouldReceive('getAttribute')->withArgs(['welcome_displayed_at'])->once()->andReturn($now);
        $supplier->shouldReceive('getAttribute')->withArgs(['take_rate'])->once()->andReturn($take_rate);
        $supplier->shouldReceive('getAttribute')->withArgs(['take_rate_until'])->once()->andReturn($take_rate_until);
        $supplier->shouldReceive('getAttribute')->withArgs(['offers_delivery'])->once()->andReturn($delivery);
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->once()->andReturn(true);

        $accountant = Mockery::mock(Staff::class);
        $accountant->shouldReceive('getAttribute')
            ->withArgs(['name'])
            ->once()
            ->andReturn($accountantName = 'Accountant');
        $accountant->shouldReceive('getAttribute')->withArgs(['phone'])->once()->andReturn($accountantPhone = '789');
        $accountant->shouldReceive('getAttribute')
            ->withArgs(['email'])
            ->once()
            ->andReturn($accountantEmail = 'accountant@supplier.com');
        $supplier->shouldReceive('getAttribute')->with('accountant')->once()->andReturn($accountant);

        $manager = Mockery::mock(Staff::class);
        $manager->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($managerName = 'Manager');
        $manager->shouldReceive('getAttribute')->withArgs(['phone'])->once()->andReturn($managerPhone = '456');
        $manager->shouldReceive('getAttribute')
            ->withArgs(['email'])
            ->once()
            ->andReturn($managerEmail = 'manager@supplier.com');
        $supplier->shouldReceive('getAttribute')->with('manager')->once()->andReturn($manager);

        $supplier->shouldReceive('getAttribute')->with('counters')->once()->andReturn([]);

        $supplier->shouldReceive('getAttribute')->with('brands')->once()->andReturn([]);

        $image = Mockery::mock(Media::class);
        $image->shouldReceive('getAttribute')->with('uuid')->once()->andReturn('0000-image');
        $image->shouldReceive('getUrl')->withNoArgs()->once()->andReturn('image url');
        $image->shouldReceive('hasGeneratedConversion')->with(MediaConversionNames::THUMB)->once()->andReturnFalse();
        $supplier->shouldReceive('getFirstMedia')->with(MediaCollectionNames::IMAGES)->once()->andReturn($image);

        $logo = Mockery::mock(Media::class);
        $logo->shouldReceive('getAttribute')->with('uuid')->once()->andReturn('0000-logo');
        $logo->shouldReceive('getUrl')->withNoArgs()->once()->andReturn('logo url');
        $logo->shouldReceive('hasGeneratedConversion')->with(MediaConversionNames::THUMB)->once()->andReturnFalse();
        $supplier->shouldReceive('getFirstMedia')->with(MediaCollectionNames::LOGO)->once()->andReturn($logo);

        $getSupplierSetting = Mockery::mock(GetSupplierSetting::class);
        $getSupplierSetting->shouldReceive('execute')->withNoArgs()->once()->andReturnTrue();
        App::bind(GetSupplierSetting::class, fn() => $getSupplierSetting);

        $resource = new BaseResource($supplier);

        $response = $resource->resolve();

        $data = [
            'id'                      => $id,
            'name'                    => $name,
            'branch'                  => $branch,
            'email'                   => $email,
            'phone'                   => $phone,
            'prokeep_phone'           => $phone,
            'address'                 => $address,
            'address_2'               => $address2,
            'zip_code'                => $zipCode,
            'city'                    => $city,
            'state'                   => $stateResource,
            'country'                 => $countryResource,
            'timezone'                => $timezone,
            'about'                   => $about,
            'open_hours'              => SupplierHourResource::collection(new Collection()),
            'verified_at'             => $now,
            'contact_phone'           => $contactPhone,
            'contact_email'           => $contactEmail,
            'contact_secondary_email' => $contactSecondaryEmail,
            'accountant_name'         => $accountantName,
            'accountant_email'        => $accountantEmail,
            'accountant_phone'        => $accountantPhone,
            'manager_name'            => $managerName,
            'manager_email'           => $managerEmail,
            'manager_phone'           => $managerPhone,
            'counter_staff'           => CounterStaffResource::collection([]),
            'brands'                  => new BrandCollection([]),
            'welcome_displayed_at'    => $now,
            'offers_delivery'         => $delivery,
            'image'                   => new ImageResource($image),
            'logo'                    => new ImageResource($logo),
            'take_rate'               => 2.5,
            'take_rate_until'         => $take_rate_until,
            'can_use_curri_delivery'  => true,
            'bid_number_required'     => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test
     * @dataProvider isCurriDeliveryEnabledProvider
     */
    public function it_get_can_use_curri_delivery_depending_is_curri_delivery_enabled(
        bool $expected
    ) {

        $id       = '123456-654321';
        $name     = 'Acme Inc.';
        $branch   = 1;
        $address  = '123 St.';
        $address2 = 'Apt. D.';
        $country  = Country::build(CountryDataType::UNITED_STATES);
        /** @var State $state */
        $state           = $country->getStates()->first();
        $countryResource = new CountryResource($country);
        $stateResource   = new StateResource($state);
        $city            = 'Warsaw';
        $zipCode         = '11111';
        $email           = 'acme@inc.com';
        $phone           = '123456789';
        $about           = 'about';
        $now             = Carbon::now();
        $delivery        = true;
        $take_rate       = Supplier::DEFAULT_TAKE_RATE;
        $take_rate_until = Carbon::create(Supplier::DEFAULT_YEAR, Supplier::DEFAULT_MONTH, Supplier::DEFAULT_DAY);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name);
        $supplier->shouldReceive('getAttribute')->withArgs(['branch'])->once()->andReturn($branch);
        $supplier->shouldReceive('getAttribute')->withArgs(['email'])->once()->andReturn($email);
        $supplier->shouldReceive('getAttribute')->withArgs(['phone'])->once()->andReturn($phone);
        $supplier->shouldReceive('getAttribute')->withArgs(['prokeep_phone'])->once()->andReturn($phone);
        $supplier->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturn($address);
        $supplier->shouldReceive('getAttribute')->withArgs(['address_2'])->once()->andReturn($address2);
        $supplier->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturn($zipCode);
        $supplier->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturn($city);
        $supplier->shouldReceive('getAttribute')->withArgs(['state'])->once()->andReturn($state->isoCode);
        $supplier->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturn($country->isoCode);
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['timezone'])
            ->once()
            ->andReturn($timezone = 'America/New_York');
        $supplier->shouldReceive('getAttribute')->withArgs(['about'])->once()->andReturn($about);
        $supplier->shouldReceive('getAttribute')->withArgs(['supplierHours'])->once()->andReturn(new Collection());
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['contact_phone'])
            ->once()
            ->andReturn($contactPhone = '1234567');
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['contact_email'])
            ->once()
            ->andReturn($contactEmail = 'contact@example.com');
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['contact_secondary_email'])
            ->once()
            ->andReturn($contactSecondaryEmail = 'contact2@example.com');
        $supplier->shouldReceive('getAttribute')->withArgs(['verified_at'])->once()->andReturn($now);
        $supplier->shouldReceive('getAttribute')->withArgs(['welcome_displayed_at'])->once()->andReturn($now);
        $supplier->shouldReceive('getAttribute')->withArgs(['take_rate'])->once()->andReturn($take_rate);
        $supplier->shouldReceive('getAttribute')->withArgs(['take_rate_until'])->once()->andReturn($take_rate_until);
        $supplier->shouldReceive('getAttribute')->withArgs(['offers_delivery'])->once()->andReturn($delivery);
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->once()->andReturn($expected);

        $accountant = Mockery::mock(Staff::class);
        $accountant->shouldReceive('getAttribute')
            ->withArgs(['name'])
            ->once()
            ->andReturn($accountantName = 'Accountant');
        $accountant->shouldReceive('getAttribute')->withArgs(['phone'])->once()->andReturn($accountantPhone = '789');
        $accountant->shouldReceive('getAttribute')
            ->withArgs(['email'])
            ->once()
            ->andReturn($accountantEmail = 'accountant@supplier.com');
        $supplier->shouldReceive('getAttribute')->with('accountant')->once()->andReturn($accountant);

        $manager = Mockery::mock(Staff::class);
        $manager->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($managerName = 'Manager');
        $manager->shouldReceive('getAttribute')->withArgs(['phone'])->once()->andReturn($managerPhone = '456');
        $manager->shouldReceive('getAttribute')
            ->withArgs(['email'])
            ->once()
            ->andReturn($managerEmail = 'manager@supplier.com');
        $supplier->shouldReceive('getAttribute')->with('manager')->once()->andReturn($manager);

        $supplier->shouldReceive('getAttribute')->with('counters')->once()->andReturn([]);

        $supplier->shouldReceive('getAttribute')->with('brands')->once()->andReturn([]);

        $image = Mockery::mock(Media::class);
        $image->shouldReceive('getAttribute')->with('uuid')->once()->andReturn('0000-image');
        $image->shouldReceive('getUrl')->withNoArgs()->once()->andReturn('image url');
        $image->shouldReceive('hasGeneratedConversion')->with(MediaConversionNames::THUMB)->once()->andReturnFalse();
        $supplier->shouldReceive('getFirstMedia')->with(MediaCollectionNames::IMAGES)->once()->andReturn($image);

        $logo = Mockery::mock(Media::class);
        $logo->shouldReceive('getAttribute')->with('uuid')->once()->andReturn('0000-logo');
        $logo->shouldReceive('getUrl')->withNoArgs()->once()->andReturn('logo url');
        $logo->shouldReceive('hasGeneratedConversion')->with(MediaConversionNames::THUMB)->once()->andReturnFalse();
        $supplier->shouldReceive('getFirstMedia')->with(MediaCollectionNames::LOGO)->once()->andReturn($logo);

        $getSupplierSetting = Mockery::mock(GetSupplierSetting::class);
        $getSupplierSetting->shouldReceive('execute')->withNoArgs()->once()->andReturnTrue();
        App::bind(GetSupplierSetting::class, fn() => $getSupplierSetting);

        $resource = new BaseResource($supplier);

        $response = $resource->resolve();

        $data = [
            'id'                      => $id,
            'name'                    => $name,
            'branch'                  => $branch,
            'email'                   => $email,
            'phone'                   => $phone,
            'prokeep_phone'           => $phone,
            'address'                 => $address,
            'address_2'               => $address2,
            'zip_code'                => $zipCode,
            'city'                    => $city,
            'state'                   => $stateResource,
            'country'                 => $countryResource,
            'timezone'                => $timezone,
            'about'                   => $about,
            'open_hours'              => SupplierHourResource::collection(new Collection()),
            'verified_at'             => $now,
            'contact_phone'           => $contactPhone,
            'contact_email'           => $contactEmail,
            'contact_secondary_email' => $contactSecondaryEmail,
            'accountant_name'         => $accountantName,
            'accountant_email'        => $accountantEmail,
            'accountant_phone'        => $accountantPhone,
            'manager_name'            => $managerName,
            'manager_email'           => $managerEmail,
            'manager_phone'           => $managerPhone,
            'counter_staff'           => CounterStaffResource::collection([]),
            'brands'                  => new BrandCollection([]),
            'welcome_displayed_at'    => $now,
            'offers_delivery'         => $delivery,
            'image'                   => new ImageResource($image),
            'logo'                    => new ImageResource($logo),
            'take_rate'               => 2.5,
            'take_rate_until'         => $take_rate_until,
            'can_use_curri_delivery'  => $expected,
            'bid_number_required'     => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    public function isCurriDeliveryEnabledProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
