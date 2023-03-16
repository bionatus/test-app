<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Auth\Email\Login;

use App\Http\Resources\LiveApi\V1\Auth\Email\Login\BaseResource;
use App\Models\Staff;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $id                   = '123456-654321';
        $initialPasswordSetAt = Carbon::now();
        $token                = '123456789ABCDEFGH';

        $staff = Mockery::mock(Staff::class);
        $staff->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $staff->shouldReceive('getAttribute')
            ->withArgs(['initial_password_set_at'])
            ->once()
            ->andReturn($initialPasswordSetAt);

        $resource = new BaseResource($staff, $token);

        $response = $resource->resolve();

        $data = [
            'id'                      => $id,
            'initial_password_set_at' => $initialPasswordSetAt,
            'token'                   => $token,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
