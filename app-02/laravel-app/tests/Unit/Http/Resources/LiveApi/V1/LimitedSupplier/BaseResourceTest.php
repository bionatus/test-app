<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\LimitedSupplier;

use App\Http\Resources\LiveApi\V1\LimitedSupplier\BaseResource;
use App\Models\Supplier;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $id       = '123456-654321';
        $take_rate = Supplier::DEFAULT_TAKE_RATE;
        $take_rate_until = Carbon::create(Supplier::DEFAULT_YEAR, Supplier::DEFAULT_MONTH, Supplier::DEFAULT_DAY);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $supplier->shouldReceive('getAttribute')->withArgs(['take_rate'])->once()->andReturn($take_rate);
        $supplier->shouldReceive('getAttribute')->withArgs(['take_rate_until'])->once()->andReturn($take_rate_until);

        $resource = new BaseResource($supplier);

        $response = $resource->resolve();

        $data = [
            'id'                => $id,
            'take_rate'         => 2.5,
            'take_rate_until'   => $take_rate_until
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
