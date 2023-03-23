<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\AuthenticateBasecampUser;
use Config;
use Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;

class AuthenticateBasecampUserTest extends TestCase
{
    use RefreshDatabase;

    private Request                  $request;
    private AuthenticateBasecampUser $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request    = Request::create('');
        $this->middleware = new AuthenticateBasecampUser();
    }

    /** @test
     * @throws UnauthorizedHttpException
     */
    public function it_should_throw_an_exception_with_invalid_token()
    {
        $key = 'test_key';
        Config::set('basecamp.token.key', $key);

        $invalidToken = 'invalid token';

        $this->request->headers->set('Authorization', 'Bearer ' . $invalidToken);

        $this->expectException(UnauthorizedHttpException::class);
        $this->middleware->handle($this->request, fn() => null);
    }

    /** @test */
    public function it_should_pass_with_valid_token()
    {
        $key = 'test_key';
        Config::set('basecamp.token.key', $key);
        $token = Hash::make($key);

        $this->request->headers->set('Authorization', 'Bearer ' . $token);

        $this->assertTrue($this->middleware->handle($this->request, fn() => true));
    }
}
