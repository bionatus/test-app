<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\Phone;

use App\Http\Resources\Api\V3\Account\Phone\CodeRequestResource;
use App\Models\Phone;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CodeRequestResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $fullNumber = 123456789;
        $time       = Carbon::now();

        $phone = \Mockery::mock(Phone::class);
        $phone->shouldReceive('fullNumber')->withNoArgs()->once()->andReturn($fullNumber);
        $phone->shouldReceive('nextRequestAvailableAt')->withNoArgs()->once()->andReturn($time);

        $resource = new class($phone) extends CodeRequestResource {
        };

        $response = $resource->resolve();

        $data = [
            'phone'                     => $fullNumber,
            'next_request_available_at' => $time,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(CodeRequestResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
