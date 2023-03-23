<?php

namespace Tests\Unit\Listeners\User;

use App;
use App\Events\Order\Delivery\Curri\OnRoute;
use App\Listeners\User\SendCurriDeliveryOnRoutePushNotification;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\User;
use App\Notifications\User\CurriDeliveryOnRoutePushNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mockery;
use Notification;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;

class SendCurriDeliveryOnRoutePushNotificationTest extends TestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_trait()
    {
        $this->assertUseTrait(SendCurriDeliveryOnRoutePushNotification::class, InteractsWithQueue::class);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendCurriDeliveryOnRoutePushNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Exception
     */
    public function it_sends_a_push_notification_to_a_technician()
    {
        Notification::fake();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('disabled_at')->once()->andReturnTrue();
        $user->shouldReceive('getKey')->withNoArgs()->times(3)->andReturn('id');
        $user->shouldReceive('notify')->passthru();
        $user->shouldReceive('shouldSendPushNotificationWithoutSetting')->withNoArgs()->once()->andReturnFalse();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('user')->once()->andReturn($user);

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('getAttribute')->with('order')->once()->andReturn($order);

        $curriDelivery = Mockery::mock(CurriDelivery::class);
        $curriDelivery->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);

        $event    = new OnRoute($curriDelivery);
        $listener = App::make(SendCurriDeliveryOnRoutePushNotification::class);
        $listener->handle($event);

        Notification::assertSentTo($user, CurriDeliveryOnRoutePushNotification::class,
            function(CurriDeliveryOnRoutePushNotification $notification) use ($order) {
                $reflection = new ReflectionClass($notification);
                $property   = $reflection->getProperty('order');
                $property->setAccessible(true);

                return $order === $property->getValue($notification);
            });
    }

    /** @test
     * @throws Exception
     */
    public function it_does_not_send_the_push_notification_when_user_not_exist()
    {
        Notification::fake();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('user')->once()->andReturnNull();

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('getAttribute')->with('order')->once()->andReturn($order);

        $curriDelivery = Mockery::mock(CurriDelivery::class);
        $curriDelivery->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);

        $event    = new OnRoute($curriDelivery);
        $listener = App::make(SendCurriDeliveryOnRoutePushNotification::class);
        $listener->handle($event);

        Notification::assertSentTimes(OnRoute::class, 0);
    }
}
