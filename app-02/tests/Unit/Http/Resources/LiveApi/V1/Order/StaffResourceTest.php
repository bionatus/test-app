<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Order;

use App\Http\Resources\LiveApi\V1\Order\StaffResource;
use App\Models\Staff;
use Mockery;
use Tests\TestCase;

class StaffResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $id   = 'uuid';
        $name = 'staff name';

        $staff = Mockery::mock(Staff::class);
        $staff->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $staff->shouldReceive('getAttribute')->with('name')->once()->andReturn($name);

        $resource = new StaffResource($staff);
        $response = $resource->resolve();

        $data = [
            'id' => $id,
            'name' => $name,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(StaffResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
