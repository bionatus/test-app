<?php

namespace Tests\Unit\Notifications\Supplier\Staff;

use App\Models\Order;
use App\Models\Setting;
use App\Models\SettingStaff;
use App\Models\Staff;
use App\Models\Supplier;
use App\Notifications\Supplier\Staff\OrderCreatedSmsNotification;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use ReflectionClass;
use Tests\TestCase;

class OrderCreatedSmsNotificationTest extends TestCase
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
        $reflection = new ReflectionClass(OrderCreatedSmsNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new OrderCreatedSmsNotification(new Order(), new Staff(), true);

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

        $notification = new OrderCreatedSmsNotification($order, $staff, $shouldSendSupplierSms);

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

        SettingStaff::factory()->create([
            'staff_id'   => $staff->getKey(),
            'setting_id' => $this->settingSms->getKey(),
            'value'      => false,
        ]);

        $expected = "BluonLive - {$order->user->fullName()} from {$order->user->companyName()} has sent a new Order Request to {$order->supplier->address}!. Do Not Reply to this text.";

        $notification = new OrderCreatedSmsNotification($order, $staff, true);
        $toTwilio     = $notification->toTwilio($supplier);

        $this->assertInstanceOf(TwilioSmsMessage::class, $toTwilio);
        $this->assertEquals($expected, $toTwilio->content);
    }
}
