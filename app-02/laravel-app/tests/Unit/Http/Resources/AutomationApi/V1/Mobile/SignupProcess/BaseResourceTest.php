<?php

namespace Tests\Unit\Http\Resources\AutomationApi\V1\Mobile\SignupProcess;

use App\Http\Resources\AutomationApi\V1\Mobile\SignupProcess\BaseResource;
use App\Models\AuthenticationCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $code               = '123456';
        $type               = 'verification';
        $authenticationCode = Mockery::mock(AuthenticationCode::class);
        $authenticationCode->shouldReceive('getAttribute')->with('code')->once()->andReturn($code);
        $authenticationCode->shouldReceive('getAttribute')->with('type')->once()->andReturn($type);

        $resource = new BaseResource($authenticationCode);
        $response = $resource->resolve();

        $data = [
            'code' => $code,
            'type' => $type,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
