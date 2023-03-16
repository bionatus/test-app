<?php

namespace Tests\Unit\Models\AuthenticationCode\Scopes;

use App\Models\AuthenticationCode;
use App\Models\AuthenticationCode\Scopes\LoginType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTypeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_login_type()
    {
        AuthenticationCode::factory()->verification()->count(2)->create();
        AuthenticationCode::factory()->login()->count(3)->create();

        $authenticationCodes = AuthenticationCode::scoped(new LoginType())->get();

        $this->assertCount(3, $authenticationCodes);
    }
}
