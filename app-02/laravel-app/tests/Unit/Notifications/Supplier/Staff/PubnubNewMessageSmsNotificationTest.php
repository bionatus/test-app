<?php

namespace Tests\Unit\Notifications\Supplier\Staff;

use App\Models\Order;
use App\Models\Setting;
use App\Models\SettingStaff;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\Supplier\Staff\PubnubNewMessageSmsNotification;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use ReflectionClass;
use Tests\TestCase;

class PubnubNewMessageSmsNotificationTest extends TestCase
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
        $reflection = new ReflectionClass(PubnubNewMessageSmsNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new PubnubNewMessageSmsNotification(new Supplier(), new User(), 'Test message', new Staff(), true);

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

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->counter()->create();
        if ($hasPhone) {
            $staff->phone = '123456';
        }

        SettingStaff::factory()->usingStaff($staff)->usingSetting($this->settingSms)->create([
            'value' => $setting,
        ]);

        $notification = new PubnubNewMessageSmsNotification($supplier, $user, 'Test Message', $staff,
            $shouldSendSupplierSms);

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
    public function it_sets_twilio_message_with_a_supplier_without_orders_of_a_user()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->counter()->create(['phone' => '123456']);

        $smsNotificationSetting = Setting::factory()
            ->groupNotification()
            ->boolean()
            ->create(['slug' => Setting::SLUG_STAFF_SMS_NOTIFICATION]);

        SettingStaff::factory()->create([
            'staff_id'   => $staff->getKey(),
            'setting_id' => $smsNotificationSetting->getKey(),
            'value'      => false,
        ]);

        $message = 'Some very very long test message that should be truncated';

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_EMAIL,
            'value' => false,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $expected = sprintf('BluonLive - You have a new message. %s from %s says: "%s". Do Not Reply to this text.',
            $user->fullName(), $user->companyName(), Str::limit($message, 40, '...'));

        $notification = new PubnubNewMessageSmsNotification($supplier, $user, $message, $staff, true);
        $toTwilio     = $notification->toTwilio($supplier);

        $this->assertInstanceOf(TwilioSmsMessage::class, $toTwilio);
        $this->assertEquals($expected, $toTwilio->content);
    }

    /** @test */
    public function it_sets_twilio_message_with_a_supplier_with_at_least_a_pending_or_pending_approval_order_of_a_user()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly(['contact_phone' => '123456']);
        $staff    = Staff::factory()->usingSupplier($supplier)->counter()->create(['phone' => '123456']);

        $smsNotificationSetting = Setting::factory()
            ->groupNotification()
            ->boolean()
            ->create(['slug' => Setting::SLUG_STAFF_SMS_NOTIFICATION]);

        SettingStaff::factory()->create([
            'staff_id'   => $staff->getKey(),
            'setting_id' => $smsNotificationSetting->getKey(),
            'value'      => false,
        ]);

        Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $message = 'Some very very long test message that should be truncated';

        $expected = sprintf('BluonLive - You have a new message. %s from %s says: "%s". Do Not Reply to this text.',
            $user->fullName(), $user->companyName(), Str::limit($message, 40, '...'));

        $notification = new PubnubNewMessageSmsNotification($supplier, $user, $message, $staff, true);
        $toTwilio     = $notification->toTwilio($supplier);

        $this->assertInstanceOf(TwilioSmsMessage::class, $toTwilio);
        $this->assertEquals($expected, $toTwilio->content);
    }

    /** @test */
    public function it_sets_twilio_message_with_a_supplier_without_pending_or_pending_approval_orders_of_a_user()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->counter()->create(['phone' => '123456']);

        $smsNotificationSetting = Setting::factory()
            ->groupNotification()
            ->boolean()
            ->create(['slug' => Setting::SLUG_STAFF_SMS_NOTIFICATION]);

        SettingStaff::factory()->create([
            'staff_id'   => $staff->getKey(),
            'setting_id' => $smsNotificationSetting->getKey(),
            'value'      => false,
        ]);

        Order::factory()->usingUser($user)->usingSupplier($supplier)->approved()->create();
        $message = 'Some very very long test message that should be truncated';

        $expected = sprintf('BluonLive - You have a new message. %s from %s says: "%s". Do Not Reply to this text.',
            $user->fullName(), $user->companyName(), Str::limit($message, 40, '...'));

        $notification = new PubnubNewMessageSmsNotification($supplier, $user, $message, $staff, true);
        $toTwilio     = $notification->toTwilio($supplier);

        $this->assertInstanceOf(TwilioSmsMessage::class, $toTwilio);
        $this->assertEquals($expected, $toTwilio->content);
    }
}
