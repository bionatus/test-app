<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\User;

use App\Http\Resources\LiveApi\V1\User\ExtendedSupplierUserResource;
use App\Http\Resources\LiveApi\V1\User\ImageResource;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\SupplierUser;
use App\Models\User;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class ExtendedSupplierUserResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $id          = '123456-654321';
        $name        = 'Trickery Jas';
        $companyName = 'Acme Inc.';
        $photo       = 'test.png';
        $zip         = '12345';

        $supplierUser = Mockery::mock(SupplierUser::class);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $user->shouldReceive('fullName')->withNoArgs()->once()->andReturn($name);
        $user->shouldReceive('photoUrl')->withNoArgs()->once()->andReturn($photo);

        $supplierUser->shouldReceive('getAttribute')->withArgs(['user'])->once()->andReturn($user);
        $supplierUser->shouldReceive('getAttribute')->withArgs(['status'])->once()->andReturn($status = 'unconfirmed');
        $supplierUser->shouldReceive('getAttribute')
            ->withArgs(['customer_tier'])
            ->once()
            ->andReturn($customerTier = 'new');
        $supplierUser->shouldReceive('getAttribute')->withArgs(['cash_buyer'])->once()->andReturnFalse();
        $supplierUser->shouldReceive('getAttribute')
            ->withArgs(['created_at'])
            ->once()
            ->andReturn($createdAt = Carbon::now());

        $company = Mockery::mock(Company::class);
        $company->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($companyName);
        $company->shouldReceive('getAttribute')->withArgs(['zip_code'])->once()->andReturn($zip);

        $companyUser = Mockery::mock(CompanyUser::class);
        $companyUser->shouldReceive('getAttribute')->withArgs(['company'])->twice()->andReturn($company);

        $user->shouldReceive('getAttribute')->withArgs(['companyUser'])->once()->andReturn($companyUser);
        $resource = new ExtendedSupplierUserResource($supplierUser);

        $response = $resource->resolve();

        $data = [
            'id'            => $id,
            'name'          => $name,
            'company'       => $companyName,
            'photo'         => ImageResource::make($photo),
            'zip'           => $zip,
            'status'        => $status,
            'customer_tier' => $customerTier,
            'cash_buyer'    => false,
            'created_at'    => $createdAt,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ExtendedSupplierUserResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $id   = '123456-654321';
        $name = 'Trickery Jas';

        $supplierUser = Mockery::mock(SupplierUser::class);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $user->shouldReceive('fullName')->withNoArgs()->once()->andReturn($name);
        $user->shouldReceive('photoUrl')->withNoArgs()->once()->andReturnNull();

        $supplierUser->shouldReceive('getAttribute')->withArgs(['user'])->once()->andReturn($user);
        $supplierUser->shouldReceive('getAttribute')->withArgs(['status'])->once()->andReturn($status = 'unconfirmed');
        $supplierUser->shouldReceive('getAttribute')->withArgs(['customer_tier'])->once()->andReturnNull();
        $supplierUser->shouldReceive('getAttribute')->withArgs(['cash_buyer'])->once()->andReturnFalse();
        $supplierUser->shouldReceive('getAttribute')
            ->withArgs(['created_at'])
            ->once()
            ->andReturn($createdAt = Carbon::now());

        $user->shouldReceive('getAttribute')->withArgs(['companyUser'])->once()->andReturnNull();
        $resource = new ExtendedSupplierUserResource($supplierUser);

        $response = $resource->resolve();

        $data = [
            'id'            => $id,
            'name'          => $name,
            'company'       => null,
            'photo'         => null,
            'zip'           => null,
            'status'        => $status,
            'customer_tier' => null,
            'cash_buyer'    => false,
            'created_at'    => $createdAt,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ExtendedSupplierUserResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
