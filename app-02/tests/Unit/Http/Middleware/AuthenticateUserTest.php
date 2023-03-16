<?php

namespace Tests\Unit\Http\Middleware;

use App;
use App\Http\Middleware\AuthenticateUser;
use App\Models\Phone;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

class AuthenticateUserTest extends TestCase
{
    use RefreshDatabase;

    private Request          $request;
    private AuthenticateUser $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request    = Request::create('');
        $this->middleware = new AuthenticateUser(App::make(JWTAuth::class));
    }

    /** @test
     * @throws JWTException
     */
    public function it_should_continue_on_legacy_user_token()
    {
        App\User::flushEventListeners();
        $user  = App\User::create([
            'id'          => 10,
            'email'       => 'john@doe.com',
            'password'    => 'pass',
            'public_name' => 'PublicName',
        ]);
        $token = \JWTAuth::fromUser($user);

        $this->request->headers->set('Authorization', 'Bearer ' . $token);

        $this->assertTrue($this->middleware->handle($this->request, fn() => true));
    }

    /** @test
     * @throws JWTException
     */
    public function it_should_continue_on_user_token()
    {
        $user  = User::factory()->create();
        $token = \JWTAuth::fromUser($user);

        $this->request->headers->set('Authorization', 'Bearer ' . $token);

        $this->assertTrue($this->middleware->handle($this->request, fn() => true));
    }

    /** @test
     * @throws JWTException
     */
    public function it_should_throw_an_exception_on_phone_token()
    {
        User::factory()->create(['id' => 10]);

        $phone = Phone::factory()->create(['id' => 10]);
        $token = \JWTAuth::fromUser($phone);

        $this->request->headers->set('Authorization', 'Bearer ' . $token);

        $this->expectException(UnauthorizedHttpException::class);
        $this->middleware->handle($this->request, fn() => null);
    }

    /** @test
     * @throws JWTException
     */
    public function it_should_throw_an_exception_on_staff_token()
    {
        User::factory()->create(['id' => 10]);

        $staff = Staff::factory()->createQuietly(['id' => 10]);
        $token = \JWTAuth::fromUser($staff);

        $this->request->headers->set('Authorization', 'Bearer ' . $token);

        $this->expectException(UnauthorizedHttpException::class);
        $this->middleware->handle($this->request, fn() => null);
    }

    /** @test
     * @throws JWTException
     */
    public function it_should_throw_an_exception_on_disabled_user()
    {
        $user = User::factory()->create(['id' => 10, 'disabled_at' => Carbon::now()]);

        $token = \JWTAuth::fromUser($user);
        $this->request->headers->set('Authorization', 'Bearer ' . $token);

        $this->expectException(AccessDeniedHttpException::class);
        $this->middleware->handle($this->request, fn() => null);
    }
}
