<?php

namespace Tests\Unit\Listeners\User;

use App;
use App\Actions\Models\SettingUser\GetNotificationSetting;
use App\Constants\RequestKeys;
use App\Events\User\NewMessage;
use App\Listeners\User\SendNewMessagePubnubNotification;
use App\Models\InternalNotification;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\User\NewMessagePubnubNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Notification;
use ReflectionClass;
use Str;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class SendNewMessagePubnubNotificationTest extends TestCase
{
    use CanRefreshDatabase;
    use WithFaker;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendNewMessagePubnubNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Exception
     */
    public function it_sends_a_notification_to_an_user()
    {
        $this->refreshDatabaseForSingleTest();

        Notification::fake();

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();

        Setting::factory()->boolean()->create([
            'slug'  => Setting::SLUG_NEW_CHAT_MESSAGE_IN_APP,
            'value' => true,
        ]);

        $event    = new NewMessage($supplier, $user, 'Test Message');
        $listener = App::make(SendNewMessagePubnubNotification::class);
        $listener->handle($event);

        Notification::assertSentTo($user, NewMessagePubnubNotification::class);
    }

    /** @test */
    public function it_creates_an_internal_notification_with_250_limited_characters_in_the_message()
    {
        $this->refreshDatabaseForSingleTest();

        $supplier = Supplier::factory()->createQuietly();
        $user     = User::factory()->create();
        $message  = $this->faker->text(300);

        $action = Mockery::mock(GetNotificationSetting::class);
        $action->shouldReceive('execute')->withNoArgs()->andReturn(true);
        App::bind(GetNotificationSetting::class, fn() => $action);

        $event = new NewMessage($supplier, $user, $message);

        $listener = App::make(SendNewMessagePubnubNotification::class);

        $listener->handle($event);

        $this->assertDatabaseHas(InternalNotification::tableName(), [
            RequestKeys::MESSAGE      => Str::limit($message, 250),
            RequestKeys::SOURCE_EVENT => 'new-message',
            RequestKeys::SOURCE_ID    => $supplier->getRouteKey(),
            RequestKeys::SOURCE_TYPE  => 'supplier',
            RequestKeys::USER_ID      => $user->getRouteKey(),
        ]);
    }
}
