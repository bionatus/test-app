<?php

namespace Tests\Unit\Http\Middleware;

use App\Constants\RouteParameters;
use App\Http\Middleware\DenyAgentGroupSettingIfNoAgentAuthenticated;
use App\Models\Agent;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Mockery;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class DenyAgentGroupSettingIfNoAgentAuthenticatedTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_continues_normally_if_setting_is_not_from_agents_group()
    {
        $setting   = new Setting();
        $routeMock = Mockery::mock(Route::class);
        $routeMock->shouldReceive('parameter')->withArgs([RouteParameters::SETTING_USER])->once()->andReturn($setting);
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('route')->withNoArgs()->once()->andReturn($routeMock);

        $middleware = new DenyAgentGroupSettingIfNoAgentAuthenticated();

        $middleware->handle($requestMock, function() {
        });

        $this->assertTrue(true); // Executing this means no error was thrown
    }

    /** @test */
    public function it_throw_exception_if_setting_is_from_agents_group_and_there_is_no_authenticated_user()
    {
        $setting   = Setting::factory()->groupAgent()->make();
        $routeMock = Mockery::mock(Route::class);
        $routeMock->shouldReceive('parameter')->withArgs([RouteParameters::SETTING_USER])->once()->andReturn($setting);
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('route')->withNoArgs()->once()->andReturn($routeMock);

        $middleware = new DenyAgentGroupSettingIfNoAgentAuthenticated();

        $this->expectException(ModelNotFoundException::class);
        $middleware->handle($requestMock, function() {
        });
    }

    /** @test */
    public function it_throw_exception_if_setting_is_from_agents_group_and_authenticated_user_is_not_agent()
    {
        $this->refreshDatabaseForSingleTest();

        $setting   = Setting::factory()->groupAgent()->make();
        $routeMock = Mockery::mock(Route::class);
        $routeMock->shouldReceive('parameter')->withArgs([RouteParameters::SETTING_USER])->once()->andReturn($setting);
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('route')->withNoArgs()->once()->andReturn($routeMock);
        $this->login(User::factory()->create());

        $middleware = new DenyAgentGroupSettingIfNoAgentAuthenticated();

        $this->expectException(ModelNotFoundException::class);
        $middleware->handle($requestMock, function() {
        });
    }

    /** @test */
    public function it_continues_normally_if_setting_is_from_agents_group_and_authenticated_user_is_agent()
    {
        $this->refreshDatabaseForSingleTest();

        $setting   = Setting::factory()->groupAgent()->make();
        $routeMock = Mockery::mock(Route::class);
        $routeMock->shouldReceive('parameter')->withArgs([RouteParameters::SETTING_USER])->once()->andReturn($setting);
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('route')->withNoArgs()->once()->andReturn($routeMock);
        $this->login(Agent::factory()->create()->user);

        $middleware = new DenyAgentGroupSettingIfNoAgentAuthenticated();

        $middleware->handle($requestMock, function() {
        });

        $this->assertTrue(true); // Executing this means no error was thrown
    }
}
