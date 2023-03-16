<?php

namespace Tests\Unit\Http\Middleware;

use App;
use App\Constants\RouteParameters;
use App\Http\Middleware\ValidateSupplierInvitation;
use App\Models\Supplier;
use App\Models\SupplierInvitation;
use App\Models\User;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ValidateSupplierInvitationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_should_reject_if_invitation_already_sent()
    {
        $supplierInvitation = SupplierInvitation::factory()->createQuietly();
        $user               = $supplierInvitation->user;
        $supplier           = $supplierInvitation->supplier;

        Auth::shouldReceive('user')->once()->andReturn($user);
        $supplierMock = Mockery::mock(Supplier::class);
        $supplierMock->shouldReceive('getKey')->withNoArgs()->once()->andReturn($supplier->getKey());
        $routeMock = Mockery::mock(Route::class);
        $routeMock->shouldReceive('parameter')->withArgs([RouteParameters::SUPPLIER])->once()->andReturn($supplierMock);
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('route')->withNoArgs()->once()->andReturn($routeMock);

        $middleware = App::make(ValidateSupplierInvitation::class);

        $response = $middleware->handle($requestMock, function() {
        });

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /** @test */
    public function it_should_accept_a_new_invitation()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();

        Auth::shouldReceive('user')->once()->andReturn($user);
        $supplierMock = Mockery::mock(Supplier::class);
        $supplierMock->shouldReceive('getKey')->withNoArgs()->once()->andReturn($supplier->getKey());
        $routeMock = Mockery::mock(Route::class);
        $routeMock->shouldReceive('parameter')->withArgs([RouteParameters::SUPPLIER])->once()->andReturn($supplierMock);
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('route')->withNoArgs()->once()->andReturn($routeMock);

        $middleware = App::make(ValidateSupplierInvitation::class);

        $this->assertTrue($middleware->handle($requestMock, fn() => true));
    }
}
