<?php

namespace Tests\Unit\Notifications\LiveApi\V1;

use App\Models\Staff;
use App\Notifications\LiveApi\V1\ResetPasswordNotification;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

class ResetPasswordNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_uses_correct_view()
    {
        $token        = Str::random(40);
        $staff        = Staff::factory()->withEmail()->createQuietly();
        $notification = new ResetPasswordNotification($token);
        $mail         = $notification->toMail($staff);
        $this->assertEquals("notifications.live-api.v1.email", $mail->markdown);
    }

    /** @test */
    public function it_has_a_correct_text_on_the_mail()
    {
        $token = Str::random(40);
        $staff = Staff::factory()->withEmail()->createQuietly();
        Config::set('live.url', 'https://live.com/');
        Config::set('live.routes.password_reset', '#/password-reset/{token}');

        $notification = new ResetPasswordNotification($token);
        $mail         = $notification->toMail($staff);

        $this->assertEquals("Reset Password Notification", $mail->subject);
        $this->assertNull($mail->greeting);
        $this->assertNull($mail->salutation);
        $this->assertContains("You are receiving this email because we received a password reset request for your account.",
            $mail->introLines);
        $this->assertEquals("Reset Password", $mail->actionText);

        $encodedEmail = urlencode($staff->email);
        $this->assertEquals("https://live.com/#/password-reset/$token?email=$encodedEmail", $mail->actionUrl);

        $expiration = Config::get('auth.passwords.live.expire');
        $this->assertContains("This password reset link will expire in $expiration minutes.", $mail->outroLines);
        $this->assertContains("If you did not request a password reset, no further action is required.",
            $mail->outroLines);
    }

    /** @test
     * @noinspection PhpCastIsUnnecessaryInspection
     */
    public function it_put_live_app_name_and_url_on_mail_header()
    {
        $token        = Str::random(40);
        $staff        = Staff::factory()->withEmail()->createQuietly();
        $notification = new ResetPasswordNotification($token);
        $appName      = 'Bluon Live App Name';
        $appUrl       = 'https://live.bluon.com/';

        Config::set('live.app_name', $appName);
        Config::set('live.url', $appUrl);

        $mailContent = $notification->toMail($staff)->render();

        $crawler = new Crawler(strval($mailContent));

        $this->assertEquals(1, $crawler->filter(".header a[href='$appUrl']")->count());
    }

    /** @test */
    public function it_uses_live_app_name_on_greetings_text()
    {
        $token        = Str::random(40);
        $staff        = Staff::factory()->withEmail()->createQuietly();
        $notification = new ResetPasswordNotification($token);
        $appName      = 'Bluon Live App Name';
        Config::set('live.app_name', $appName);

        $mailContent = $notification->toMail($staff)->render();
        $this->assertStringContainsString("Regards,<br>\n$appName", $mailContent);
    }

    /** @test
     * @noinspection PhpCastIsUnnecessaryInspection
     */
    public function it_uses_live_app_name_on_mail_footer()
    {
        $token        = Str::random(40);
        $staff        = Staff::factory()->withEmail()->createQuietly();
        $notification = new ResetPasswordNotification($token);
        $appName      = 'Bluon Live App Name';
        $year         = date('Y');
        Config::set('live.app_name', $appName);

        $mailContent = $notification->toMail($staff)->render();

        $crawler = new Crawler(strval($mailContent));
        $this->assertStringContainsString("© $year $appName. All rights reserved.",
            $crawler->filter(".footer")->html());
    }
}
