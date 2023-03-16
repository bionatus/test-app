<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Order;

use App\Http\Resources\LiveApi\V1\Order\SupplierResource;
use App\Models\Supplier;
use Mockery;
use Tests\TestCase;

class SupplierResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $id       = 'uuid';
        $name     = 'name';
        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name);

        $resource = new SupplierResource($supplier);

        $response = $resource->resolve();

        $data = [
            'id'   => $id,
            'name' => $name,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SupplierResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
