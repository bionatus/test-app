<?php

namespace Tests\Unit\Events\Order\Delivery\Curri;

use App\Events\Order\Delivery\Curri\OnRoute;
use App\Listeners\User\SendCurriDeliveryOnRoutePubnubMessage;
use App\Listeners\User\SendCurriDeliveryOnRoutePushNotification;
use App\Listeners\User\SendCurriDeliveryOnRouteSmsNotification;
use App\Models\CurriDelivery;
use Tests\TestCase;

class OnRouteTest extends TestCase
{
    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(OnRoute::class, [
            SendCurriDeliveryOnRoutePubnubMessage::class,
            SendCurriDeliveryOnRoutePushNotification::class,
            SendCurriDeliveryOnRouteSmsNotification::class,
        ]);
    }

    /** @test */
    public function it_returns_its_curri_delivery()
    {
        $curriDelivery = new CurriDelivery();

        $event = new OnRoute($curriDelivery);

        $this->assertSame($curriDelivery, $event->curriDelivery());
    }
}
