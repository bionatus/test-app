<?php

namespace Tests\Unit\Events\Order;

use App\Events\Order\DeliveryEtaUpdated;
use App\Listeners\Order\Delivery\Curri\CalculateUserConfirmationTime;
use App\Listeners\Order\Delivery\SendOrderEtaUpdatedInAppNotification;
use Tests\TestCase;

class DeliveryEtaUpdatedTest extends TestCase
{
    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(DeliveryEtaUpdated::class, [
            CalculateUserConfirmationTime::class,
            SendOrderEtaUpdatedInAppNotification::class,
        ]);
    }
}
