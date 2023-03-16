<?php

namespace Tests\Unit\Http\Requests\Api\V3\Account\Phone\Verify;

use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\Api\V3\Account\Phone\Verify\InvokeRequest;
use App\Models\AuthenticationCode;
use App\Models\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Lang;
use Mockery;
use Tests\Unit\Http\Requests\RequestTestCase;

class InvokeRequestTest extends RequestTestCase
{
    protected string $requestClass = InvokeRequest::class;
    use RefreshDatabase;

    /** @test */
    public function it_requires_a_code()
    {
        $phone = Phone::factory()->unverified()->create();

        $request = $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::UNVERIFIED_PHONE, $phone->fullNumber());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CODE]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::CODE]),
        ]);
    }

    /** @test */
    public function its_code_must_be_an_integer()
    {
        $phone = Phone::factory()->unverified()->create();

        $request = $this->formRequest($this->requestClass, [RequestKeys::CODE => '012345'])
            ->addRouteParameter(RouteParameters::UNVERIFIED_PHONE, $phone->fullNumber());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CODE]);
        $request->assertValidationMessages([
            Lang::get('validation.integer', ['attribute' => RequestKeys::CODE]),
        ]);
    }

    /** @test */
    public function its_code_must_be_exactly_six_digits()
    {
        $phone = Phone::factory()->unverified()->create();

        $request = $this->formRequest($this->requestClass, [RequestKeys::CODE => 12345])
            ->addRouteParameter(RouteParameters::UNVERIFIED_PHONE, $phone->fullNumber());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CODE]);
        $request->assertValidationMessages([
            Lang::get('validation.digits', ['attribute' => RequestKeys::CODE, 'digits' => 6]),
        ]);
    }

    /** @test */
    public function its_code_must_exist_for_the_bound_phone()
    {
        $valid   = AuthenticationCode::factory()->create(['code' => 123456]);
        $invalid = AuthenticationCode::factory()->create(['code' => 654321]);

        $request = $this->formRequest($this->requestClass, [RequestKeys::CODE => $valid->code])
            ->addRouteParameter(RouteParameters::UNVERIFIED_PHONE, $invalid->phone->fullNumber());
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CODE]);
        $request->assertValidationMessages([
            Lang::get('validation.exists', ['attribute' => RequestKeys::CODE]),
        ]);
    }

    /** @test */
    public function it_passes_validation_on_valid_data()
    {
        $authenticationCode = AuthenticationCode::factory()->create();
        $phone              = $authenticationCode->phone;

        $request = $this->formRequest($this->requestClass, [RequestKeys::CODE => $authenticationCode->code])
            ->addRouteParameter(RouteParameters::UNVERIFIED_PHONE, $phone->fullNumber());

        $request->assertValidationPassed();
    }

    /** @test */
    public function it_returns_a_phone()
    {
        $route = Mockery::mock(Route::class);

        $route->shouldReceive('parameter')
            ->withArgs([RouteParameters::UNVERIFIED_PHONE, null])
            ->once()
            ->andReturn($phone = new Phone());

        $request = InvokeRequest::create('', 'POST', []);
        $request->setRouteResolver(fn() => $route);

        $this->assertSame($phone, $request->phone());
    }
}
