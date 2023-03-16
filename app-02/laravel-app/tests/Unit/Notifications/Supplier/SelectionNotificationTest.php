<?php

namespace Tests\Unit\Notifications\Supplier;

use App\Models\Setting;
use App\Models\Supplier;
use App\NotificationChannels\TwilioByProkeepPhoneChannel;
use App\Notifications\Supplier\SelectionNotification;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use ReflectionClass;
use Tests\TestCase;

class SelectionNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SelectionNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new SelectionNotification(new Supplier());

        $this->assertEquals('database', $job->connection);
    }

    /** @test */
    public function it_is_sent_via_twilio_channel_if_requirements_are_met()
    {
        Config::set('notifications.sms.enabled', true);

        $supplier = Supplier::factory()->createQuietly(['contact_phone' => '123456']);

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_EMAIL,
            'value' => false,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new SelectionNotification($supplier);

        $this->assertContains(TwilioChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_twilio_channel_if_supplier_has_not_phone()
    {
        Config::set('notifications.sms.enabled', true);

        $supplier = Supplier::factory()->createQuietly();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new SelectionNotification($supplier);

        $this->assertNotContains(TwilioChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_twilio_channel_if_config_notifications_sms_enabled_is_false()
    {
        Config::set('notifications.sms.enabled', false);

        $supplier = Supplier::factory()->createQuietly(['contact_phone' => '123456']);

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new SelectionNotification($supplier);

        $this->assertNotContains(TwilioChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_twilio_channel_if_sms_notification_setting_is_false()
    {
        Config::set('notifications.sms.enabled', true);

        $supplier = Supplier::factory()->createQuietly(['contact_phone' => '123456']);

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_SMS,
            'value' => false,
        ]);

        $notification = new SelectionNotification($supplier);

        $this->assertNotContains(TwilioChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_is_sent_via_twilio_by_prokeep_phone_channel_if_requirements_are_met()
    {
        Config::set('notifications.sms.enabled', true);

        $supplier = Supplier::factory()->createQuietly(['prokeep_phone' => '123456']);

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_EMAIL,
            'value' => false,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new SelectionNotification($supplier);

        $this->assertContains(TwilioByProkeepPhoneChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_twilio_by_prokeep_phone_channel_if_supplier_has_not_prokeep_phone()
    {
        Config::set('notifications.sms.enabled', true);

        $supplier = Supplier::factory()->createQuietly();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new SelectionNotification($supplier);

        $this->assertNotContains(TwilioByProkeepPhoneChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_twilio_by_prokeep_phone_channel_if_config_notifications_sms_enabled_is_false(
    )
    {
        Config::set('notifications.sms.enabled', false);

        $supplier = Supplier::factory()->createQuietly(['prokeep_phone' => '123456']);

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new SelectionNotification($supplier);

        $this->assertNotContains(TwilioByProkeepPhoneChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_twilio_channel_by_prokeep_phone_if_sms_notification_setting_is_false()
    {
        Config::set('notifications.sms.enabled', true);

        $supplier = Supplier::factory()->createQuietly(['prokeep_phone' => '123456']);

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_SMS,
            'value' => false,
        ]);

        $notification = new SelectionNotification($supplier);

        $this->assertNotContains(TwilioByProkeepPhoneChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_is_sent_via_email_channel_if_supplier_has_contact_email_and_email_notification_setting_is_true()
    {
        $supplier = Supplier::factory()->createQuietly(['contact_email' => 'example@devbase.us']);

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_SMS,
            'value' => false,
        ]);

        $notification = new SelectionNotification($supplier);

        $this->assertContains('mail', $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_email_channel_if_supplier_has_not_contact_email()
    {
        $supplier = Supplier::factory()->createQuietly();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new SelectionNotification($supplier);

        $this->assertNotContains('mail', $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_email_channel_if_email_notification_setting_is_false()
    {
        $supplier = Supplier::factory()->createQuietly(['contact_email' => 'example@devbase.us']);

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_EMAIL,
            'value' => false,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new SelectionNotification($supplier);

        $this->assertNotContains('mail', $notification->via($supplier));
    }

    /** @test */
    public function it_sets_twilio_message()
    {
        $supplier = Supplier::factory()->createQuietly(['contact_phone' => '123456']);

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_EMAIL,
            'value' => false,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_SMS,
            'value' => true,
        ]);

        Config::set('live.url', $baseLiveUrl = 'https://test.com/');
        Config::set('live.account.customers', $accountUrl = '#/test-account');

        $linkUrl = $baseLiveUrl . $accountUrl;

        $expected = (new TwilioSmsMessage())->content("BluonLive - You have a new Bluon Member to be Verified ✨ Link: {$linkUrl}. Do Not Reply to this text.");

        $notification = new SelectionNotification($supplier);
        $toTwilio     = $notification->toTwilio($supplier);

        $this->assertInstanceOf(TwilioSmsMessage::class, $toTwilio);
        $this->assertEquals($expected, $toTwilio);
    }

    /** @test */
    public function it_has_correct_email_recipients()
    {
        $supplier = Supplier::factory()->createQuietly([
            'name'                    => 'John Doe',
            'contact_email'           => 'example@example.com',
            'contact_secondary_email' => 'secondary@example.com',
        ]);

        $notification = new SelectionNotification($supplier);
        $mail         = $notification->toMail($supplier);

        $this->assertEquals([
            [
                'name'    => 'John Doe',
                'address' => 'example@example.com',
            ],
        ], $mail->to);

        $this->assertEquals([
            [
                'name'    => 'John Doe',
                'address' => 'secondary@example.com',
            ],
        ], $mail->bcc);
    }
}
