<?php

namespace Tests\Unit\Notifications\Supplier\Staff;

use App\Models\Order;
use App\Models\Setting;
use App\Models\SettingStaff;
use App\Models\Staff;
use App\Models\Supplier;
use App\Notifications\Supplier\Staff\OrderApprovedSmsNotification;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use ReflectionClass;
use Tests\TestCase;

class OrderApprovedSmsNotificationTest extends TestCase
{
    use RefreshDatabase;

    private $settingSms;

    protected function setUp(): void
    {
        parent::setUp();
        $this->settingSms = Setting::factory()
            ->applicableToStaff()
            ->create(['slug' => Setting::SLUG_STAFF_SMS_NOTIFICATION, 'value' => false]);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(OrderApprovedSmsNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new OrderApprovedSmsNotification(new Order(), new Staff(), true);

        $this->assertEquals('database', $job->connection);
    }

    /**
     * @test
     * @dataProvider twilioChannelEnabledProvider
     */
    public function it_is_sent_via_twilio_channel_if_requirements_are_met(
        bool $expected,
        bool $hasPhone,
        bool $smsNotificationEnabled,
        bool $setting,
        bool $shouldSendSupplierSms
    ) {
        Config::set('notifications.sms.enabled', $smsNotificationEnabled);

        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->counter()->create();
        $order    = Order::factory()->usingSupplier($supplier)->create();
        if ($hasPhone) {
            $staff->phone = '123456';
        }

        SettingStaff::factory()->usingStaff($staff)->usingSetting($this->settingSms)->create([
            'value' => $setting,
        ]);

        $notification = new OrderApprovedSmsNotification($order, $staff, $shouldSendSupplierSms);

        $this->assertEquals($expected, in_array(TwilioChannel::class, $notification->via($staff)));
    }

    public function twilioChannelEnabledProvider(): array
    {
        return [
            //expected, hasPhone, smsNotificationEnabled, setting, shouldSendSupplierSms
            [true, true, true, true, true],
            [false, true, true, false, true],
            [false, true, false, true, true],
            [false, true, false, false, true],
            [false, false, true, true, true],
            [false, false, true, false, true],
            [false, false, false, true, true],
            [false, false, false, false, true],
            [false, true, true, true, false],
            [false, true, true, false, false],
            [false, true, false, true, false],
            [false, true, false, false, false],
            [false, false, true, true, false],
            [false, false, true, false, false],
            [false, false, false, true, false],
            [false, false, false, false, false],
        ];
    }

    /** @test */
    public function it_sets_twilio_message()
    {
        Config::set('notifications.sms.enabled', true);

        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->counter()->create(['phone' => '123456']);
        $order    = Order::factory()->usingSupplier($supplier)->create();

        $smsNotificationSetting = Setting::factory()
            ->groupNotification()
            ->boolean()
            ->create(['slug' => Setting::SLUG_STAFF_SMS_NOTIFICATION]);

        SettingStaff::factory()->create([
            'staff_id'   => $staff->getKey(),
            'setting_id' => $smsNotificationSetting->getKey(),
            'value'      => true,
        ]);

        $notification = new OrderApprovedSmsNotification($order, $staff, true);

        $this->assertInstanceOf(TwilioSmsMessage::class, $notification->toTwilio($staff));
    }

    /** @test */
    public function it_sets_twilio_message_correctly_with_order_name()
    {
        $supplier = Supplier::factory()->createQuietly(['address' => '123 Street', 'phone' => '123456']);
        $staff    = Staff::factory()->usingSupplier($supplier)->counter()->create(['phone' => '123456']);
        $order    = Order::factory()->usingSupplier($supplier)->create(['name' => 'test order 123']);

        $smsNotificationSetting = Setting::factory()
            ->groupNotification()
            ->boolean()
            ->create(['slug' => Setting::SLUG_STAFF_SMS_NOTIFICATION]);

        SettingStaff::factory()->create([
            'staff_id'   => $staff->getKey(),
            'setting_id' => $smsNotificationSetting->getKey(),
            'value'      => true,
        ]);

        $notification = new OrderApprovedSmsNotification($order, $staff, true);

        Config::set('live.url', $baseLiveUrl = 'https://test.com/');
        Config::set('live.routes.outbound', $outboundUrl = '#/test-outbound');

        $linkUrl = $baseLiveUrl . $outboundUrl;

        $expected = "BluonLive - Order {$order->name} has been approved ✅ Link: {$linkUrl}. Do Not Reply to this text.";

        $this->assertEquals($expected, $notification->toTwilio($staff)->content);
    }

    /** @test */
    public function it_sets_twilio_message_correctly_without_order_name()
    {
        $supplier = Supplier::factory()->createQuietly(['address' => '123 Street', 'phone' => '123456']);
        $staff    = Staff::factory()->usingSupplier($supplier)->counter()->create(['phone' => '123456']);
        $order    = Order::factory()->usingSupplier($supplier)->create();

        $smsNotificationSetting = Setting::factory()
            ->groupNotification()
            ->boolean()
            ->create(['slug' => Setting::SLUG_STAFF_SMS_NOTIFICATION]);

        SettingStaff::factory()->create([
            'staff_id'   => $staff->getKey(),
            'setting_id' => $smsNotificationSetting->getKey(),
            'value'      => true,
        ]);

        $notification = new OrderApprovedSmsNotification($order, $staff, true);

        Config::set('live.url', $baseLiveUrl = 'https://test.com/');
        Config::set('live.routes.outbound', $outboundUrl = '#/test-outbound');

        $linkUrl  = $baseLiveUrl . $outboundUrl;
        $expected = "BluonLive - Order Bid #{$order->bid_number} has been approved ✅ Link: {$linkUrl}. Do Not Reply to this text.";

        $this->assertEquals($expected, $notification->toTwilio($staff)->content);
    }
}
