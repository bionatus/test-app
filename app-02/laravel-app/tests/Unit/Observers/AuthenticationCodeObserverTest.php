<?php

namespace Tests\Unit\Observers;

use App\Models\AuthenticationCode;
use App\Observers\AuthenticationCodeObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationCodeObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_code_when_creating_if_no_code_was_set()
    {
        $authenticationCode = AuthenticationCode::factory()->make(['code' => null]);

        $observer = new AuthenticationCodeObserver();

        $observer->creating($authenticationCode);

        $this->assertNotNull($authenticationCode->code);
    }

    /** @test */
    public function it_does_not_fill_code_when_creating_if_code_was_already_set()
    {
        $code               = '000000';
        $authenticationCode = AuthenticationCode::factory()->make(['code' => $code]);

        $observer = new AuthenticationCodeObserver();

        $observer->creating($authenticationCode);

        $this->assertSame($code, $authenticationCode->code);
    }
}
