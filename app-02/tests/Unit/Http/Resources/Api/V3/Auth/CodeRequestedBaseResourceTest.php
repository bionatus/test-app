<?php

namespace Tests\Unit\Http\Resources\Api\V3\Auth;

use App\Http\Resources\Api\V3\Auth\CodeRequestedResource;
use App\Models\Phone;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class CodeRequestedBaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $fullNumber = 123456789;
        $time       = Carbon::now();

        $phone = Mockery::mock(Phone::class);
        $phone->shouldReceive('fullNumber')->withNoArgs()->once()->andReturn($fullNumber);
        $phone->shouldReceive('nextRequestAvailableAt')->withNoArgs()->once()->andReturn($time);
        $phone->shouldReceive('isVerifiedAndAssigned')->withNoArgs()->once()->andReturn(true);

        $resource = new class($phone) extends CodeRequestedResource {
        };
        $response = $resource->resolve();

        $data = [
            'phone'                     => $fullNumber,
            'next_request_available_at' => $time,
            'type'                      => CodeRequestedResource::TYPE_SIGN_IN,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(CodeRequestedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test
     * @dataProvider typeProvider
     */
    public function it_returns_a_correct_type(bool $isVerified, string $expected)
    {
        $fullNumber = 123456789;
        $time       = Carbon::now();

        $phone = Mockery::mock(Phone::class);
        $phone->shouldReceive('fullNumber')->withNoArgs()->once()->andReturn($fullNumber);
        $phone->shouldReceive('nextRequestAvailableAt')->withNoArgs()->once()->andReturn($time);
        $phone->shouldReceive('isVerifiedAndAssigned')->withNoArgs()->once()->andReturn($isVerified);

        $resource = new class($phone) extends CodeRequestedResource {
        };
        $response = $resource->resolve();

        $this->assertArrayHasKeyAndValue('type', $expected, $response);
    }

    public function typeProvider(): array
    {
        return [
            [false, CodeRequestedResource::TYPE_SIGN_UP],
            [true, CodeRequestedResource::TYPE_SIGN_IN],
        ];
    }
}
