<?php

namespace Tests\Unit\Http\Middleware;

use App;
use App\Http\Middleware\AuthenticateStaff;
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

class AuthenticateStaffTest extends TestCase
{
    use RefreshDatabase;

    private Request           $request;
    private AuthenticateStaff $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = Request::create('');
        Auth::shouldUse('live');
        $this->middleware = new AuthenticateStaff(App::make(JWTAuth::class));
    }

    /** @test
     * @throws JWTException
     */
    public function it_should_throw_an_exception_on_legacy_user_token()
    {
        Staff::factory()->createQuietly(['id' => 10]);
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
    public function it_should_throw_an_exception_on_phone_token()
    {
        $user  = Phone::factory()->create();
        $token = \JWTAuth::fromUser($user);

        $this->request->headers->set('Authorization', 'Bearer ' . $token);

        $this->expectException(UnauthorizedHttpException::class);
        $this->middleware->handle($this->request, fn() => null);
    }

    /** @test
     * @throws JWTException
     */
    public function it_should_pass_with_staff_token()
    {
        User::factory()->create(['id' => 10]);
        Phone::factory()->create(['id' => 10]);

        $staff = Staff::factory()->createQuietly(['id' => 10]);
        $token = \JWTAuth::fromUser($staff);

        $this->request->headers->set('Authorization', 'Bearer ' . $token);

        $this->assertTrue($this->middleware->handle($this->request, fn() => true));
    }
}
