<?php

namespace Tests\Unit\Http\Middleware;

use App\Constants\RouteParameters;
use App\Http\Middleware\ValidateIfPhoneCanMakeSMSRequests;
use App\Models\Phone;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Mockery;
use Tests\TestCase;

class ValidateIfPhoneCanMakeSMSRequestsTest extends TestCase
{
    /** @test
     * @throws Exception
     */
    public function it_check_that_the_phone_can_make_SMS_requests()
    {
        $phoneMock = Mockery::mock(Phone::class);
        $phoneMock->shouldReceive('nextRequestAvailableAt')->withNoArgs()->once()->andReturn(Carbon::now()->subSecond());
        $routeMock = Mockery::mock(Route::class);
        $routeMock->shouldReceive('parameter')
            ->withArgs([RouteParameters::ASSIGNED_VERIFIED_PHONE])
            ->once()
            ->andReturn($phoneMock);
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('route')->withNoArgs()->once()->andReturn($routeMock);

        $middleware = new ValidateIfPhoneCanMakeSMSRequests();

        $this->assertTrue($middleware->handle($requestMock, fn() => true));
    }

    /** @test
     * @throws Exception
     */
    public function it_throw_exception_if_phone_can_not_make_SMS_requests()
    {
        $phoneMock = Mockery::mock(Phone::class);
        $phoneMock->shouldReceive('nextRequestAvailableAt')->withNoArgs()->once()->andReturn(Carbon::now()->addSecond());
        $routeMock = Mockery::mock(Route::class);
        $routeMock->shouldReceive('parameter')
            ->withArgs([RouteParameters::ASSIGNED_VERIFIED_PHONE])
            ->once()
            ->andReturn($phoneMock);
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('route')->withNoArgs()->once()->andReturn($routeMock);

        $middleware = new ValidateIfPhoneCanMakeSMSRequests();

        $this->expectException(Exception::class);
        $middleware->handle($requestMock, fn() => null);
    }
}
