<?php

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Notifications\CreatePasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use URL;

class CreatePasswordNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_a_correct_text_on_the_mail()
    {
        $user         = User::factory()->create();
        $notification = new CreatePasswordNotification($user);
        $mail         = $notification->toMail($user);

        $this->assertEquals("Create New Password Notification", $mail->subject);
        $this->assertNull($mail->greeting);
        $this->assertNull($mail->salutation);
        $this->assertContains("You are receiving this email because we received a create password request for your account.",
            $mail->introLines);
        $this->assertEquals("Create Password", $mail->actionText);
        $this->assertEquals(URL::route('password.reset', ['token' => $notification->token, 'email' => $user->email]),
            $mail->actionUrl);
        $this->assertContains("This link will expire in 60 minutes.", $mail->outroLines);
        $this->assertContains("If you did not request a password creation, no further action is required.",
            $mail->outroLines);
    }
}
