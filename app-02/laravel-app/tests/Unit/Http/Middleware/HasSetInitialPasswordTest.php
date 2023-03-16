<?php

namespace Tests\Unit\Http\Middleware;

use App;
use App\Http\Middleware\HasSetInitialPassword;
use App\Models\Staff;
use Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Request;
use Tests\TestCase;

class HasSetInitialPasswordTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fails_if_has_not_set_initial_password()
    {
        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly(['initial_password_set_at' => null]));

        $request = Request::instance();

        $middleware = App::make(HasSetInitialPassword::class);

        $this->expectException(AuthorizationException::class);
        $middleware->handle($request, function() {
        });
    }

    /** @test */
    public function it_passes_if_has_set_initial_password()
    {
        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly(['initial_password_set_at' => Carbon::now()]));

        $request = Request::instance();

        $middleware = App::make(HasSetInitialPassword::class);

        $response = $middleware->handle($request, function() {
            return true;
        });

        $this->assertTrue($response);
    }
}
