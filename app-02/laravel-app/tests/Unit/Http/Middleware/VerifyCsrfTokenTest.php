<?php

namespace Tests\Unit\Http\Middleware;

use App;
use App\Http\Middleware\VerifyCsrfToken;
use Config;
use ReflectionProperty;
use Tests\TestCase;

class VerifyCsrfTokenTest extends TestCase
{
    /** @test */
    public function it_fills_except_paths_with_config()
    {
        Config::set('session.csrf_except_paths', $except = ['path']);

        $middleware         = App::make(VerifyCsrfToken::class);
        $reflectionProperty = new ReflectionProperty(VerifyCsrfToken::class, 'except');
        $reflectionProperty->setAccessible(true);

        $this->assertSame($except, $reflectionProperty->getValue($middleware));
    }
}
