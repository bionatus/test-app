<?php

namespace Tests\Unit\Http\Requests\Api\V3\Twilio\Webhooks\Auth\Call;

use App;
use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\Twilio\Webhooks\Auth\Call\InvokeRequest;
use App\Models\AuthenticationCode;
use App\Rules\Phone\FullNumberExist;
use Lang;
use Mockery;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

class InvokeRequestTest extends RequestTestCase
{
    protected string $requestClass = InvokeRequest::class;

    /** @test */
    public function it_requires_a_to_parameter()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TWILIO_UPPER_TO]);
        $attribute = Str::lower(RequestKeys::TWILIO_UPPER_TO);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_to_parameter_must_exist_in_our_records()
    {
        $fullNumberExist = Mockery::mock(FullNumberExist::class);
        $fullNumberExist->shouldReceive('passes')->withAnyArgs()->once()->andReturnFalse();
        $fullNumberExist->shouldReceive('message')->withAnyArgs()->once()->andReturn('A message');
        App::bind(FullNumberExist::class, fn() => $fullNumberExist);
        $request = $this->formRequest($this->requestClass, [RequestKeys::TWILIO_UPPER_TO => '+5555555555']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TWILIO_UPPER_TO]);
        $request->assertValidationMessages(['A message']);
    }

    /** @test */
    public function it_can_not_ask_for_an_authentication_code_before_validation()
    {
        $request = new InvokeRequest();

        $this->expectError();

        $this->assertNull($request->authenticationCode());
    }

    /** @test */
    public function it_returns_authentication_code_if_validation_passes()
    {
        $authenticationCode = AuthenticationCode::factory()->make(['phone_id' => 1]);

        $rule = Mockery::mock(FullNumberExist::class);
        $rule->shouldReceive('authenticationCode')->withNoArgs()->once()->andReturn($authenticationCode);
        App::bind(FullNumberExist::class, fn() => $rule);

        $request = new InvokeRequest();

        $this->assertSame($authenticationCode, $request->authenticationCode());
    }
}
