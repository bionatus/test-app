<?php

namespace Tests\Unit\Http\Resources\LiveApi\V2\Supplier\Staff;

use App\Http\Resources\LiveApi\V2\Supplier\Staff\BaseResource;
use App\Models\Staff;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $id        = 'uuid';

        $staff = Mockery::mock(Staff::class);
        $staff->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $staff->shouldReceive('getAttribute')->with('name')->once()->andReturnNull();

        $resource = new BaseResource($staff);
        $response = $resource->resolve();

        $data = [
            'id'         => $id,
            'name'       => null,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $id        = 'uuid';
        $name      = 'staff name';

        $staff = Mockery::mock(Staff::class);
        $staff->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $staff->shouldReceive('getAttribute')->with('name')->once()->andReturn($name);

        $resource = new BaseResource($staff);
        $response = $resource->resolve();

        $data = [
            'id'         => $id,
            'name'       => $name,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
