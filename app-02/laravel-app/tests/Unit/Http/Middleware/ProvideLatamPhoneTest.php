<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\ProvideLatamPhone;
use Auth;
use Request;
use Tests\TestCase;

class ProvideLatamPhoneTest extends TestCase
{
    /** @test */
    public function it_should_set_phone_provider_model()
    {
        $middleware = new ProvideLatamPhone();

        Auth::shouldReceive('shouldUse')->with('phone')->once();

        $middleware->handle(Request::instance(), fn() => null);
    }
}
