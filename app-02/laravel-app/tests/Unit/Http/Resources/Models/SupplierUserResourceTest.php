<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\SupplierUserResource;
use App\Models\SupplierUser;
use Mockery;
use Tests\TestCase;

class SupplierUserResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $supplierUser = Mockery::mock(SupplierUser::class);
        $supplierUser->shouldReceive('getAttribute')->withArgs(['status'])->once()->andReturn($status = 'unconfirmed');
        $supplierUser->shouldReceive('getAttribute')
            ->withArgs(['customer_tier'])
            ->once()
            ->andReturn($customerTier = 'new');
        $supplierUser->shouldReceive('getAttribute')->withArgs(['cash_buyer'])->once()->andReturnFalse();

        $resource = new SupplierUserResource($supplierUser);
        $response = $resource->resolve();

        $data = [
            'status'        => $status,
            'customer_tier' => $customerTier,
            'cash_buyer'    => false,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SupplierUserResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $supplierUser = Mockery::mock(SupplierUser::class);
        $supplierUser->shouldReceive('getAttribute')->withArgs(['status'])->once()->andReturn($status = 'unconfirmed');
        $supplierUser->shouldReceive('getAttribute')->withArgs(['customer_tier'])->once()->andReturnNull();
        $supplierUser->shouldReceive('getAttribute')->withArgs(['cash_buyer'])->once()->andReturnFalse();

        $resource = new SupplierUserResource($supplierUser);
        $response = $resource->resolve();

        $data = [
            'status'        => $status,
            'customer_tier' => null,
            'cash_buyer'    => false,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SupplierUserResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
