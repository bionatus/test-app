<?php

namespace Tests\Unit\Models\AuthenticationCode\Scopes;

use App\Models\AuthenticationCode;
use App\Models\AuthenticationCode\Scopes\VerificationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationTypeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_verification_type()
    {
        AuthenticationCode::factory()->login()->count(2)->create();
        AuthenticationCode::factory()->verification()->count(3)->create();

        $authenticationCodes = AuthenticationCode::scoped(new VerificationType())->get();

        $this->assertCount(3, $authenticationCodes);
    }
}
