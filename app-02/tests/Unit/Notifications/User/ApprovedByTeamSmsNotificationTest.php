<?php

namespace Tests\Unit\Notifications\User;

use App\Models\Order;
use App\Models\Phone;
use App\Models\Setting;
use App\Models\SettingUser;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\User\ApprovedByTeamSmsNotification;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use ReflectionClass;
use Tests\TestCase;

class ApprovedByTeamSmsNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(ApprovedByTeamSmsNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $supplier     = Supplier::factory()->createQuietly();
        $order        = Order::factory()->usingSupplier($supplier)->create();
        $notification = new ApprovedByTeamSmsNotification($order);

        $this->assertEquals('database', $notification->connection);
    }

    /**
     * @test
     * @dataProvider twilioChannelEnabledProvider
     */
    public function it_is_sent_via_twilio_channel_if_requirements_are_met(
        bool $expected,
        bool $hasPhone,
        bool $smsNotificationEnabled,
        bool $settingValue
    ) {
        Config::set('notifications.sms.enabled', $smsNotificationEnabled);
        $user         = User::factory()->create();
        $supplier     = Supplier::factory()->createQuietly();
        $order        = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $notification = new ApprovedByTeamSmsNotification($order);
        $setting      = Setting::factory()->applicableToUser()->create([
            'slug'  => Setting::SLUG_ORDER_APPROVED_BY_YOUR_TEAM_SMS,
            'value' => true,
        ]);

        if ($hasPhone) {
            Phone::factory()->usingUser($user)->create();
        }

        SettingUser::factory()->usingUser($user)->usingSetting($setting)->create(['value' => $settingValue]);

        $this->assertEquals($expected, in_array(TwilioChannel::class, $notification->via($user)));
    }

    public function twilioChannelEnabledProvider(): array
    {
        return [
            [true, true, true, true],
            [false, true, true, false],
            [false, true, false, true],
            [false, true, false, false],
            [false, false, true, true],
            [false, false, true, false],
            [false, false, false, true],
            [false, false, false, false],
        ];
    }

    /**
     * @test
     */
    public function it_sets_twilio_message()
    {
        $supplier = Supplier::factory()->createQuietly();
        $user     = User::factory()->create();
        $order    = Order::factory()->usingSupplier($supplier)->usingUser($user)->create();

        $smsText = 'Bluon - Your team has approved your quote via shared link. Do Not Reply to this text.';

        $expected = (new TwilioSmsMessage())->content($smsText);

        $notification = new ApprovedByTeamSmsNotification($order);
        $toTwilio     = $notification->toTwilio($user);

        $this->assertInstanceOf(TwilioSmsMessage::class, $toTwilio);
        $this->assertEquals($expected, $toTwilio);
    }
}
