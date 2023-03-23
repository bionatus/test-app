<?php

namespace Tests\Unit\Models;

use App\Models\AuthenticationCode;

class SmsCodeTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(AuthenticationCode::tableName(), [
            'id',
            'phone_id',
            'type',
            'code',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_knows_if_it_is_login()
    {
        $login        = AuthenticationCode::factory()->login()->make();
        $verification = AuthenticationCode::factory()->verification()->make();

        $this->assertTrue($login->isLogin());
        $this->assertFalse($verification->isLogin());
    }

    /** @test */
    public function it_fills_code_on_creation()
    {
        $authenticationCode = AuthenticationCode::factory()->make(['code' => null]);
        $authenticationCode->save();

        $this->assertNotNull($authenticationCode->code);
    }
}
