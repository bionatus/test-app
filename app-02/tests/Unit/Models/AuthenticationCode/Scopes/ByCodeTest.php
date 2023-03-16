<?php

namespace Tests\Unit\Models\AuthenticationCode\Scopes;

use App\Models\AuthenticationCode;
use App\Models\AuthenticationCode\Scopes\ByCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByCodeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_code()
    {
        $code = 123456;
        AuthenticationCode::factory()->count(2)->create(['code' => 654321]);
        AuthenticationCode::factory()->count(3)->create(['code' => $code]);

        $authenticationCodes = AuthenticationCode::scoped(new ByCode($code))->get();

        $this->assertCount(3, $authenticationCodes);
    }
}
