<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\ProvideLatamUser;
use Auth;
use Request;
use Tests\TestCase;

class ProvideLatamUserTest extends TestCase
{
    /** @test */
    public function it_should_set_user_provider_model()
    {
        $middleware = new ProvideLatamUser();

        Auth::shouldReceive('shouldUse')->with('latam')->once();

        $middleware->handle(Request::instance(), fn() => null);
    }
}
