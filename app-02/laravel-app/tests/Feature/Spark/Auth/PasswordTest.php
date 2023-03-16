<?php

namespace Tests\Feature\Spark\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Password;
use Tests\TestCase;

class PasswordTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_should_mark_user_as_registration_completed_when_reset_password()
    {
        $user  = User::factory()->create();
        $data  = [
            'token'                 => Password::broker()->createToken($user),
            'email'                 => $user->email,
            'password'              => '12345678',
            'password_confirmation' => '12345678',
        ];
        $route = URL::route('password.reset.save', $data);
        $this->post($route);

        $this->assertDatabaseHas('users', [
            'id'                     => $user->id,
            'registration_completed' => true,
        ]);
    }
}
