<?php

namespace Tests\Unit\Models\AppNotification\Scopes;

use App\AppNotification;
use App\Models\AppNotification\Scopes\Unread;
use Database\Factories\AppNotificationFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnreadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_read_status()
    {
        (new AppNotificationFactory())->count(10)->create();
        (new AppNotificationFactory())->read()->count(10)->create();

        $unreadAppNotifications = AppNotification::scoped(new Unread())->get();

        $this->assertCount(10, $unreadAppNotifications);
    }
}
