<?php

namespace Tests\Unit\Http\Resources\Api\V3\Auth\Phone\Register\Verify;

use App\Http\Resources\Api\V3\Auth\Phone\Register\Verify\BaseResource;
use App\Models\Phone;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $id    = 1234567890;
        $token = 'a valid token';

        $phone = Mockery::mock(Phone::class);
        $phone->shouldReceive('fullNumber')->withNoArgs()->once()->andReturn($id);
        $resource = new BaseResource($phone, $token);

        $response = $resource->resolve();

        $data = [
            'id'    => $id,
            'token' => $token,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
