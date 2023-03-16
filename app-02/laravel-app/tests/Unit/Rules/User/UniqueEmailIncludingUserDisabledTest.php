<?php

namespace Tests\Unit\Rules\User;

use App\Models\User;
use App\Rules\User\UniqueEmailIncludingUserDisabled;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UniqueEmailIncludingUserDisabledTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_true_if_user_is_not_disabled()
    {
        User::factory()->create(['email' => $email = 'email@email.com']);

        $rule = new UniqueEmailIncludingUserDisabled();

        $this->assertTrue($rule->passes('email', $email));
    }

    /** @test */
    public function it_returns_false_if_user_is_disabled()
    {
        User::factory()->create(['email' => $email = 'email@email.com', 'disabled_at' => Carbon::now()]);

        $rule = new UniqueEmailIncludingUserDisabled();

        $this->assertFalse($rule->passes('email', $email));
    }
}
