<?php

namespace Tests\Unit\Notifications\Supplier;

use App\Models\Order;
use App\Models\Setting;
use App\Models\Supplier;
use App\NotificationChannels\TwilioByProkeepPhoneChannel;
use App\Notifications\Supplier\OrderCanceledByUserNotification;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use ReflectionClass;
use Tests\TestCase;

class OrderCanceledByUserNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(OrderCanceledByUserNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new OrderCanceledByUserNotification(new Order());

        $this->assertEquals('database', $job->connection);
    }

    /** @test */
    public function it_is_sent_via_twilio_channel_if_requirements_are_met()
    {
        Config::set('notifications.sms.enabled', true);

        $supplier = Supplier::factory()->createQuietly(['contact_phone' => '123456']);
        $order    = Order::factory()->usingSupplier($supplier)->createQuietly();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_EMAIL,
            'value' => false,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new OrderCanceledByUserNotification($order);

        $this->assertContains(TwilioChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_twilio_channel_if_supplier_has_not_phone()
    {
        Config::set('notifications.sms.enabled', true);

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->createQuietly();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new OrderCanceledByUserNotification($order);

        $this->assertNotContains(TwilioChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_twilio_channel_if_config_notifications_sms_enabled_is_false()
    {
        Config::set('notifications.sms.enabled', false);

        $supplier = Supplier::factory()->createQuietly(['contact_phone' => '123456']);
        $order    = Order::factory()->usingSupplier($supplier)->createQuietly();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new OrderCanceledByUserNotification($order);

        $this->assertNotContains(TwilioChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_twilio_channel_if_sms_notification_setting_is_false()
    {
        Config::set('notifications.sms.enabled', true);

        $supplier = Supplier::factory()->createQuietly(['contact_phone' => '123456']);
        $order    = Order::factory()->usingSupplier($supplier)->createQuietly();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_SMS,
            'value' => false,
        ]);

        $notification = new OrderCanceledByUserNotification($order);

        $this->assertNotContains(TwilioChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_is_sent_via_twilio_by_prokeep_phone_channel_if_requirements_are_met()
    {
        Config::set('notifications.sms.enabled', true);

        $supplier = Supplier::factory()->createQuietly(['prokeep_phone' => '123456']);
        $order    = Order::factory()->usingSupplier($supplier)->createQuietly();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_EMAIL,
            'value' => false,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new OrderCanceledByUserNotification($order);

        $this->assertContains(TwilioByProkeepPhoneChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_twilio_by_prokeep_phone_channel_if_supplier_has_not_prokeep_phone()
    {
        Config::set('notifications.sms.enabled', true);

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->createQuietly();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new OrderCanceledByUserNotification($order);

        $this->assertNotContains(TwilioByProkeepPhoneChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_twilio_by_prokeep_phone_channel_if_config_notifications_sms_enabled_is_false(
    )
    {
        Config::set('notifications.sms.enabled', false);

        $supplier = Supplier::factory()->createQuietly(['prokeep_phone' => '123456']);
        $order    = Order::factory()->usingSupplier($supplier)->createQuietly();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new OrderCanceledByUserNotification($order);

        $this->assertNotContains(TwilioByProkeepPhoneChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_twilio_by_prokeep_phone_channel_if_sms_notification_setting_is_false()
    {
        Config::set('notifications.sms.enabled', true);

        $supplier = Supplier::factory()->createQuietly(['prokeep_phone' => '123456']);
        $order    = Order::factory()->usingSupplier($supplier)->createQuietly();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_SMS,
            'value' => false,
        ]);

        $notification = new OrderCanceledByUserNotification($order);

        $this->assertNotContains(TwilioByProkeepPhoneChannel::class, $notification->via($supplier));
    }

    /** @test */
    public function it_is_sent_via_email_channel_if_supplier_has_contact_email_and_email_notification_setting_is_true()
    {
        $supplier = Supplier::factory()->createQuietly(['contact_email' => 'example@devbase.us']);
        $order    = Order::factory()->usingSupplier($supplier)->createQuietly();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_SMS,
            'value' => false,
        ]);

        $notification = new OrderCanceledByUserNotification($order);

        $this->assertContains('mail', $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_email_channel_if_supplier_has_not_contact_email()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->createQuietly();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new OrderCanceledByUserNotification($order);

        $this->assertNotContains('mail', $notification->via($supplier));
    }

    /** @test */
    public function it_should_not_send_via_email_channel_if_email_notification_setting_is_false()
    {
        $supplier = Supplier::factory()->createQuietly(['contact_email' => 'example@devbase.us']);
        $order    = Order::factory()->usingSupplier($supplier)->createQuietly();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_EMAIL,
            'value' => false,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $notification = new OrderCanceledByUserNotification($order);

        $this->assertNotContains('mail', $notification->via($supplier));
    }

    /** @test */
    public function it_sets_twilio_message()
    {
        $supplier = Supplier::factory()->createQuietly(['contact_phone' => '123456']);
        $order    = Order::factory()->usingSupplier($supplier)->createQuietly();
        $user     = $order->user;

        $smsText = "BluonLive - Bid#: $order->bid_number at $supplier->address location has been cancelled by {$user->fullName()} from {$user->companyName()}. Do Not Reply to this text.";

        $expected = (new TwilioSmsMessage())->content($smsText);

        $notification = new OrderCanceledByUserNotification($order);
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
        $order    = Order::factory()->usingSupplier($supplier)->createQuietly();

        $notification = new OrderCanceledByUserNotification($order);
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
