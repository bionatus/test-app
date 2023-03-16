<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\Phone\Sms;

use App\Http\Resources\Api\V3\Account\Phone\Sms\BaseResource;
use App\Models\Phone;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $fullNumber = 123456789;
        $time       = Carbon::now();

        $phone = Mockery::mock(Phone::class);
        $phone->shouldReceive('fullNumber')->withNoArgs()->once()->andReturn($fullNumber);
        $phone->shouldReceive('nextRequestAvailableAt')->withNoArgs()->once()->andReturn($time);

        $resource = new BaseResource($phone);
        $response = $resource->resolve();

        $data = [
            'phone'                     => $fullNumber,
            'next_request_available_at' => $time,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
