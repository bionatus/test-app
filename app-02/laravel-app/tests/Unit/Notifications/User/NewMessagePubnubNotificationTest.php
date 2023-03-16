<?php

namespace Tests\Unit\Notifications\User;

use App;
use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Models\InternalNotification;
use App\Models\Phone;
use App\Models\Setting;
use App\Models\SettingUser;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\SendsPushNotification;
use App\Notifications\User\NewMessagePubnubNotification;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Tests\TestCase;

class NewMessagePubnubNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_trait()
    {
        $this->assertUseTrait(NewMessagePubnubNotification::class, SendsPushNotification::class, ['via']);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(NewMessagePubnubNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $user         = User::factory()->create();
        $supplier     = Supplier::factory()->createQuietly();
        $notification = new NewMessagePubnubNotification($supplier, $user, 'message');

        $this->assertEquals('database', $notification->connection);
    }

    /** @test
     * @dataProvider fcmDataProvider
     */
    public function it_is_sent_via_fcm_if_requirements_are_met(
        bool $expected,
        bool $configValue,
        bool $settingValue,
        bool $enabled
    ) {
        Config::set('notifications.push.enabled', $configValue);
        $user         = User::factory()->create(['disabled_at' => $enabled ? null : Carbon::now()]);
        $supplier     = Supplier::factory()->createQuietly();
        $notification = new NewMessagePubnubNotification($supplier, $user, 'message');

        $setting = Setting::factory()->applicableToUser()->create([
            'slug'  => Setting::SLUG_NEW_CHAT_MESSAGE_IN_APP,
            'value' => true,
        ]);

        SettingUser::factory()->usingSetting($setting)->usingUser($user)->create(['value' => $settingValue]);

        $this->assertSame($expected, in_array(FcmChannel::class, $notification->via(null)));
    }

    public function fcmDataProvider(): array
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

    /** @test
     * @dataProvider smsDataProvider
     */
    public function it_is_sent_via_sms_if_requirements_are_met(
        bool $expected,
        bool $configValue,
        bool $settingValue,
        bool $phone
    ) {
        Config::set('notifications.push.enabled', false);
        Config::set('notifications.sms.enabled', $configValue);
        $user = User::factory()->create();

        if ($phone) {
            Phone::factory()->usingUser($user)->create();
        }

        $supplier     = Supplier::factory()->createQuietly();
        $notification = new NewMessagePubnubNotification($supplier, $user, 'message');

        $setting = Setting::factory()->applicableToUser()->create([
            'slug'  => Setting::SLUG_NEW_CHAT_MESSAGE_SMS,
            'value' => true,
        ]);

        SettingUser::factory()->usingSetting($setting)->usingUser($user)->create(['value' => $settingValue]);

        $this->assertSame($expected, in_array(TwilioChannel::class, $notification->via(null)));
    }

    public function smsDataProvider(): array
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

    /** @test
     * @dataProvider internalNotificationDataProvider
     */
    public function it_creates_an_internal_notification_if_requirements_are_met($enabled)
    {
        Notification::fake();

        $user     = User::factory()->create(['disabled_at' => $enabled ? null : Carbon::now()]);
        $supplier = Supplier::factory()->createQuietly();

        new NewMessagePubnubNotification($supplier, $user, $message = 'message');

        $internalNotification = [
            'message'      => $message,
            'source_event' => 'new-message',
            'source_type'  => 'supplier',
            'source_id'    => $supplier->getRouteKey(),
        ];

        if ($enabled) {
            $this->assertDatabaseHas(InternalNotification::tableName(), $internalNotification);
        } else {
            $this->assertDatabaseMissing(InternalNotification::tableName(), $internalNotification);
        }
    }

    public function internalNotificationDataProvider(): array
    {
        return [[true], [false]];
    }

    /** @test */
    public function it_sets_twilio_message()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly(['name' => $supplierName = 'Fake name']);
        $toTwilio = (new NewMessagePubnubNotification($supplier, $user, $message = 'message'))->toTwilio(null);

        $smsMessage = "Bluon - $supplierName sent you a message: \"$message\". Do Not Reply to this text.";
        $expected   = (new TwilioSmsMessage())->content($smsMessage);

        $this->assertInstanceOf(TwilioSmsMessage::class, $toTwilio);
        $this->assertEquals($expected, $toTwilio);
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_has_a_correct_fcm_message_and_payload()
    {
        Notification::fake();

        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly();
        $pubnubChannel = App::make(GetPubnubChannel::class, [
            'supplier' => $supplier,
            'user'     => $user,
        ])->execute();

        $notification = new NewMessagePubnubNotification($supplier, $user, $message = 'message');
        $fcmMessage   = $notification->toFcm();

        $this->assertInstanceOf(FcmMessage::class, $fcmMessage);

        $property = new ReflectionProperty($notification, 'internalNotification');
        $property->setAccessible(true);

        $data = [
            'source' => json_encode([
                'event'                    => 'new-message',
                'type'                     => 'supplier',
                'id'                       => $supplier->getRouteKey(),
                'internal_notification_id' => $property->getValue($notification)->getRouteKey(),
                'channel_id'               => $pubnubChannel->getRouteKey(),
                'supplier_data'            => ['id' => $supplier->getRouteKey()],
            ]),
        ];
        $this->assertArrayHasKeysAndValues($data, $fcmMessage->getData());

        $fcmNotification = $fcmMessage->getNotification();
        $title           = 'New Message from ' . $supplier->name;

        $this->assertEquals($message, $fcmNotification->getBody());
        $this->assertEquals($title, $fcmNotification->getTitle());
    }
}
