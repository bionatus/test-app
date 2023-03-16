<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\ProvideLiveUser;
use Auth;
use Request;
use Tests\TestCase;

class ProvideLiveUserTest extends TestCase
{
    /** @test */
    public function it_should_set_user_provider_model()
    {
        $middleware = new ProvideLiveUser();

        Auth::shouldReceive('shouldUse')->with('live')->once();

        $middleware->handle(Request::instance(), fn() => null);
    }
}
