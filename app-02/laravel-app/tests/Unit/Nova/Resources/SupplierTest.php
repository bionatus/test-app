<?php

namespace Tests\Unit\Nova\Resources;

use App\Models\Staff;
use App\Models\Supplier;
use App\Nova\Resources;
use Illuminate\Http\Request;
use Mockery;

class SupplierTest extends ResourceTestCase
{
    /** @test */
    public function it_uses_correct_model()
    {
        $this->assertSame(Supplier::class, Resources\Supplier::$model);
    }

    /** @test */
    public function it_uses_the_name_as_title()
    {
        $this->assertSame('name', Resources\Supplier::$title);
    }

    /** @test */
    public function it_uses_fields_for_search()
    {
        $this->assertSame([
            'id',
            'airtable_id',
            'name',
            'email',
            'address',
            'city',
        ], Resources\Supplier::$search);
    }

    /** @test */
    public function it_should_be_displayed_in_navigation()
    {
        $this->assertTrue(Resources\Supplier::$displayInNavigation);
    }

    /** @test */
    public function it_uses_current_as_group()
    {
        $this->assertEquals('Current', Resources\Supplier::group());
    }

    /** @test */
    public function it_has_expected_fields()
    {
        $this->assertHasExpectedFields(Resources\Supplier::class, [
            'id',
            'airtable_id',
            'name',
            'logo',
            'images',
            'branch',
            'address',
            'address_2',
            'timezone',
            'country',
            'state',
            'display_state',
            'city',
            'zip_code',
            'email',
            'password',
            'phone',
            'prokeep_phone',
            'offers_delivery',
            'published_at',
            'about',
            'take_rate',
            'take_rate_until',
            'terms',
            'supplierCompany',
            'contact_phone',
            'contact_email',
            'contact_secondary_email',
            Staff::TYPE_MANAGER . '_name',
            Staff::TYPE_MANAGER . '_phone',
            Staff::TYPE_MANAGER . '_email',
            Staff::TYPE_ACCOUNTANT . '_name',
            Staff::TYPE_ACCOUNTANT . '_phone',
            Staff::TYPE_ACCOUNTANT . '_email',
            'location',
            'supplierHours',
            'counters',
        ]);
    }

    /** @test */
    public function it_uses_correct_fields_for_subtitle()
    {
        $id      = '12345';
        $address = '123 East Side';
        $city    = 'New York';
        $state   = 'New York';
        $zipCode = '00000';
        $country = 'US';

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getAttribute')->withArgs(['id'])->once()->andReturn($id);
        $supplier->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturn($address);
        $supplier->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturn($city);
        $supplier->shouldReceive('getAttribute')->withArgs(['state'])->once()->andReturn($state);
        $supplier->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturn($zipCode);
        $supplier->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturn($country);

        $supplierResource = new Resources\Supplier($supplier);

        $data = implode(', ', array_filter([
            $id,
            $address,
            $city,
            $state,
            $zipCode,
            $country,
        ]));

        $this->assertSame($data, $supplierResource->subtitle());
    }

    /** @test */
    public function it_does_not_authorize_the_resource_deletion()
    {
        $supplier         = Mockery::mock(Supplier::class);
        $supplierResource = new Resources\Supplier($supplier);

        $this->assertSame(false, $supplierResource->authorizedToDelete(new Request()));
    }
}
