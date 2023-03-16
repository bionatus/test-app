<?php

namespace Tests\Unit\Notifications\Supplier;

use App\Models\Order;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\NotificationChannels\TwilioByProkeepPhoneChannel;
use App\Notifications\Supplier\PubnubNewMessageNotification;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use ReflectionClass;
use Tests\TestCase;

class PubnubNewMessageNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(PubnubNewMessageNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new PubnubNewMessageNotification(new Supplier(), new User(), 'Test message');

        $this->assertEquals('database', $job->connection);
    }

    /** @test */
    public function it_is_sent_via_twilio_channel_if_requirements_are_met()
    {
        Config::set('notifications.sms.enabled', true);

        $supplier = Supplier::factory()->createQuietly(['contact_phone' => '123456']);
        $user     = User::factory()->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_EMAIL,
            'value' => false,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new PubnubNewMessageNotification($supplier, $user, 'Test Message');

        $this->assertContains(TwilioChannel::class, $notification->via($user));
    }

    /** @test */
    public function it_should_not_send_via_twilio_channel_if_supplier_has_not_phone()
    {
        Config::set('notifications.sms.enabled', true);

        $supplier = Supplier::factory()->createQuietly();
        $user     = User::factory()->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new PubnubNewMessageNotification($supplier, $user, 'Test Message');

        $this->assertNotContains(TwilioChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_twilio_channel_if_config_notifications_sms_enabled_is_false()
    {
        Config::set('notifications.sms.enabled', false);

        $supplier = Supplier::factory()->createQuietly(['contact_phone' => '123456']);
        $user     = User::factory()->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new PubnubNewMessageNotification($supplier, $user, 'Test Message');

        $this->assertNotContains(TwilioChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_twilio_channel_if_sms_notification_setting_is_false()
    {
        Config::set('notifications.sms.enabled', true);

        $supplier = Supplier::factory()->createQuietly(['contact_phone' => '123456']);
        $user     = User::factory()->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_SMS,
            'value' => false,
        ]);

        $notification = new PubnubNewMessageNotification($supplier, $user, 'Test Message');

        $this->assertNotContains(TwilioChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_is_sent_via_twilio_by_prokeep_phone_channel_if_requirements_are_met()
    {
        Config::set('notifications.sms.enabled', true);

        $supplier = Supplier::factory()->createQuietly(['prokeep_phone' => '123456']);
        $user     = User::factory()->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_EMAIL,
            'value' => false,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new PubnubNewMessageNotification($supplier, $user, 'Test Message');

        $this->assertContains(TwilioByProkeepPhoneChannel::class, $notification->via($user));
    }

    /** @test */
    public function it_should_not_send_via_twilio_by_prokeep_phone_channel_if_supplier_has_not_prokeep_phone()
    {
        Config::set('notifications.sms.enabled', true);

        $supplier = Supplier::factory()->createQuietly();
        $user     = User::factory()->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new PubnubNewMessageNotification($supplier, $user, 'Test Message');

        $this->assertNotContains(TwilioByProkeepPhoneChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_twilio_by_prokeep_phone_channel_if_config_notifications_sms_enabled_is_false(
    )
    {
        Config::set('notifications.sms.enabled', false);

        $supplier = Supplier::factory()->createQuietly(['prokeep_phone' => '123456']);
        $user     = User::factory()->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new PubnubNewMessageNotification($supplier, $user, 'Test Message');

        $this->assertNotContains(TwilioByProkeepPhoneChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_twilio_by_prokeep_phone_channel_if_sms_notification_setting_is_false()
    {
        Config::set('notifications.sms.enabled', true);

        $supplier = Supplier::factory()->createQuietly(['prokeep_phone' => '123456']);
        $user     = User::factory()->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_SMS,
            'value' => false,
        ]);

        $notification = new PubnubNewMessageNotification($supplier, $user, 'Test Message');

        $this->assertNotContains(TwilioByProkeepPhoneChannel::class, $notification->via($user));
    }

    /** @test */
    public function it_is_sent_via_email_channel_if_supplier_has_contact_email_and_email_notification_setting_is_true()
    {
        $supplier = Supplier::factory()->createQuietly(['contact_email' => 'example@devbase.us']);
        $user     = User::factory()->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_SMS,
            'value' => false,
        ]);

        $notification = new PubnubNewMessageNotification($supplier, $user, 'Test Message');

        $this->assertContains('mail', $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_email_channel_if_supplier_has_not_contact_email()
    {
        $supplier = Supplier::factory()->createQuietly();
        $user     = User::factory()->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new PubnubNewMessageNotification($supplier, $user, 'Test Message');

        $this->assertNotContains('mail', $notification->via($supplier));
    }

    /** @test */
    public function it_is_sent_via_email_channel_if_email_notification_setting_is_false()
    {
        $supplier = Supplier::factory()->createQuietly(['contact_email' => 'example@devbase.us']);
        $user     = User::factory()->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_EMAIL,
            'value' => false,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new PubnubNewMessageNotification($supplier, $user, 'Test Message');

        $this->assertNotContains('mail', $notification->via($supplier));
    }

    /** @test */
    public function it_sets_twilio_message_with_a_supplier_without_orders_of_a_user()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly(['contact_phone' => '123456']);
        $message  = 'Some very very long test message that should be truncated';

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_EMAIL,
            'value' => false,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $smsTextReplaced = sprintf('BluonLive - You have a new message. %s from %s says: "%s". Do Not Reply to this text.',
            $user->fullName(), $user->companyName(), Str::limit($message, 40, '...'));

        $expected = (new TwilioSmsMessage())->content($smsTextReplaced);

        $notification = new PubnubNewMessageNotification($supplier, $user, $message);
        $toTwilio     = $notification->toTwilio($supplier);

        $this->assertInstanceOf(TwilioSmsMessage::class, $toTwilio);
        $this->assertEquals($expected, $toTwilio);
    }

    /** @test */
    public function it_sets_twilio_message_with_a_supplier_with_at_least_a_pending_or_pending_approval_order_of_a_user()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly(['contact_phone' => '123456']);
        Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $message = 'Some very very long test message that should be truncated';

        $smsTextReplaced = sprintf('BluonLive - You have a new message. %s from %s says: "%s". Do Not Reply to this text.',
            $user->fullName(), $user->companyName(), Str::limit($message, 40, '...'));

        $expected = (new TwilioSmsMessage())->content($smsTextReplaced);

        $notification = new PubnubNewMessageNotification($supplier, $user, $message);
        $toTwilio     = $notification->toTwilio($supplier);

        $this->assertInstanceOf(TwilioSmsMessage::class, $toTwilio);
        $this->assertEquals($expected, $toTwilio);
    }

    /** @test */
    public function it_sets_twilio_message_with_a_supplier_without_pending_or_pending_approval_orders_of_a_user()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly(['contact_phone' => '123456']);
        Order::factory()->usingUser($user)->usingSupplier($supplier)->approved()->create();
        $message = 'Some very very long test message that should be truncated';

        $smsTextReplaced = sprintf('BluonLive - You have a new message. %s from %s says: "%s". Do Not Reply to this text.',
            $user->fullName(), $user->companyName(), Str::limit($message, 40, '...'));

        $expected = (new TwilioSmsMessage())->content($smsTextReplaced);

        $notification = new PubnubNewMessageNotification($supplier, $user, $message);
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
        $user     = User::factory()->create();
        $message  = 'This is a test message';

        $notification = new PubnubNewMessageNotification($supplier, $user, $message);
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
