<?php

namespace Tests\Feature\Spark\Auth\Password;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Password;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_should_mark_user_as_registration_completed_when_reset_password()
    {
        \App\User::flushEventListeners();

        $user  = User::factory()->create();
        $data  = [
            'token'                 => Password::broker()->createToken($user),
            'email'                 => $user->email,
            'password'              => '12345678',
            'password_confirmation' => '12345678',
        ];
        $route = URL::route('password.reset.save', $data);

        $response = $this->post($route);

        $response->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseHas('users', [
            'id'                     => $user->getKey(),
            'registration_completed' => true,
        ]);
    }
}
