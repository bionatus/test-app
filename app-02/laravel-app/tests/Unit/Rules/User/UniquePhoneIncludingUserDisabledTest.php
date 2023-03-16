<?php

namespace Tests\Unit\Rules\User;

use App\Models\Phone;
use App\Models\User;
use App\Rules\User\UniquePhoneIncludingUserDisabled;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UniquePhoneIncludingUserDisabledTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_true_if_there_is_no_phone()
    {
        $rule = new UniquePhoneIncludingUserDisabled();

        $this->assertTrue($rule->passes('phone', '123456'));
    }

    /** @test */
    public function it_returns_true_if_user_is_not_disabled()
    {
        $user = User::factory()->create();
        Phone::factory()->usingUser($user)->create(['number' => $number = '123456']);

        $rule = new UniquePhoneIncludingUserDisabled();

        $this->assertTrue($rule->passes('phone', $number));
    }

    /** @test */
    public function it_returns_false_if_user_is_disabled()
    {
        $user = User::factory()->create(['disabled_at' => Carbon::now()]);
        Phone::factory()->usingUser($user)->create(['number' => $number = '123456']);

        $rule = new UniquePhoneIncludingUserDisabled();

        $this->assertFalse($rule->passes('phone', $number));
    }
}
