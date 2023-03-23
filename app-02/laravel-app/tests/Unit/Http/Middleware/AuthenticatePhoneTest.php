<?php

namespace Tests\Unit\Http\Middleware;

use App;
use App\Http\Middleware\AuthenticatePhone;
use App\Models\Phone;
use App\Models\Staff;
use App\Models\User;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

class AuthenticatePhoneTest extends TestCase
{
    use RefreshDatabase;

    private Request           $request;
    private AuthenticatePhone $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = Request::create('');
        Auth::shouldUse('phone');
        $this->middleware = new AuthenticatePhone(App::make(JWTAuth::class));
    }

    /** @test
     * @throws JWTException
     */
    public function it_should_throw_an_exception_on_legacy_user_token()
    {
        Phone::factory()->create(['id' => 10]);
        App\User::flushEventListeners();
        $user  = App\User::create([
            'id'          => 10,
            'email'       => 'john@doe.com',
            'password'    => 'pass',
            'public_name' => 'PublicName',
        ]);
        $token = \JWTAuth::fromUser($user);

        $this->request->headers->set('Authorization', 'Bearer ' . $token);

        $this->expectException(UnauthorizedHttpException::class);
        $this->middleware->handle($this->request, fn() => null);
    }

    /** @test
     * @throws JWTException
     */
    public function it_should_throw_an_exception_on_user_token()
    {
        $user  = User::factory()->create();
        $token = \JWTAuth::fromUser($user);

        $this->request->headers->set('Authorization', 'Bearer ' . $token);

        $this->expectException(UnauthorizedHttpException::class);
        $this->middleware->handle($this->request, fn() => null);
    }

    /** @test
     * @throws JWTException
     */
    public function it_should_throw_an_exception_on_staff_token()
    {
        $staff = Staff::factory()->createQuietly();
        $token = \JWTAuth::fromUser($staff);

        $this->request->headers->set('Authorization', 'Bearer ' . $token);

        $this->expectException(UnauthorizedHttpException::class);
        $this->middleware->handle($this->request, fn() => null);
    }

    /** @test
     * @throws JWTException
     */
    public function it_should_pass_with_phone_token()
    {
        User::factory()->create(['id' => 10]);

        $phone = Phone::factory()->create(['id' => 10]);
        $token = \JWTAuth::fromUser($phone);

        $this->request->headers->set('Authorization', 'Bearer ' . $token);

        $this->assertTrue($this->middleware->handle($this->request, fn() => true));
    }
}
