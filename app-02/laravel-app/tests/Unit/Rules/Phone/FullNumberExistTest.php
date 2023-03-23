<?php

namespace Tests\Unit\Rules\Phone;

use App\Models\AuthenticationCode;
use App\Models\Phone;
use App\Rules\Phone\FullNumberExist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FullNumberExistTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_does_not_pass_if_full_phone_number_is_not_properly_formatted()
    {
        $rule = new FullNumberExist();

        $this->assertFalse($rule->passes('attribute', '5555555555'));
        $this->assertSame('The :attribute is not properly formatted.', $rule->message());
    }

    /** @test */
    public function it_does_not_pass_if_full_phone_number_is_not_a_number()
    {
        $rule = new FullNumberExist();

        $this->assertFalse($rule->passes('attribute', '+5invalid'));
        $this->assertSame('The :attribute is invalid.', $rule->message());
    }

    /** @test */
    public function it_does_not_pass_if_full_phone_number_does_not_exist_in_database()
    {
        $rule = new FullNumberExist();

        $phone = Phone::factory()->make();
        $this->assertFalse($rule->passes('attribute', '+' . $phone->fullNumber()));
        $this->assertSame('The :attribute does not exist in our records.', $rule->message());
    }

    /** @test */
    public function it_does_not_pass_if_full_phone_number_does_not_have_an_authentication_code()
    {
        $rule = new FullNumberExist();

        $phone = Phone::factory()->create();
        $this->assertFalse($rule->passes('attribute', '+' . $phone->fullNumber()));
        $this->assertSame('The :attribute does not have an authentication code.', $rule->message());
    }

    /** @test */
    public function it_passes()
    {
        $authenticationCode = AuthenticationCode::factory()->create();
        $rule               = new FullNumberExist();

        $this->assertTrue($rule->passes('attribute', '+' . $authenticationCode->phone->fullNumber()));
    }

    /** @test */
    public function it_can_not_ask_for_an_authentication_code_before_validation()
    {
        $rule = new FullNumberExist();

        $this->expectError();

        $this->assertNull($rule->authenticationCode());
    }

    /** @test */
    public function it_returns_authentication_code_if_validation_passes()
    {
        $authenticationCode = AuthenticationCode::factory()->create();
        $rule               = new FullNumberExist();
        $rule->passes('attribute', '+' . $authenticationCode->phone->fullNumber());

        $this->assertSame($authenticationCode->getKey(), $rule->authenticationCode()->getKey());
    }
}
