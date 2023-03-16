<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\AcceptsJSON;
use Request;
use Tests\TestCase;

class AcceptsJSONTest extends TestCase
{
    /** @test */
    public function it_adds_accept_json_header()
    {
        $request = Request::instance();

        $middleware = new AcceptsJSON();

        $middleware->handle($request, function() {
        });

        $this->assertTrue($request->hasHeader('accept'));
        $this->assertSame('application/json', $request->header('accept'));
    }
}
