<?php

namespace Tests\Unit\Http\Middleware;

use App;
use App\Http\Middleware\AuthenticateAutomationUser;
use Config;
use Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthenticateAutomationUserTest extends TestCase
{
    use RefreshDatabase;

    private Request                    $request;
    private AuthenticateAutomationUser $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request    = Request::create('');
        $this->middleware = new AuthenticateAutomationUser();
    }

    /** @test
     * @throws UnauthorizedHttpException
     */
    public function it_should_throw_an_exception_with_invalid_token()
    {
        $token = 'invalid token';

        $this->request->headers->set('Authorization', 'Bearer ' . $token);

        $this->expectException(UnauthorizedHttpException::class);
        $this->middleware->handle($this->request, fn() => null);
    }

    /** @test
     * @throws UnauthorizedHttpException
     */
    public function it_should_throw_an_exception_in_production_environment()
    {
        $key = 'test_key';
        Config::set('automation.token.key', $key);
        $token = Hash::make($key);

        App::shouldReceive('isProduction')->andReturnTrue();

        $this->request->headers->set('Authorization', 'Bearer ' . $token);

        $this->expectException(UnauthorizedHttpException::class);
        $this->middleware->handle($this->request, fn() => null);
    }

    /** @test
     * @throws JWTException
     */
    public function it_should_pass_with_valid_token()
    {
        $key = 'test_key';
        Config::set('automation.token.key', $key);
        $token = Hash::make($key);

        $this->request->headers->set('Authorization', 'Bearer ' . $token);

        $this->assertTrue($this->middleware->handle($this->request, fn() => true));
    }
}
