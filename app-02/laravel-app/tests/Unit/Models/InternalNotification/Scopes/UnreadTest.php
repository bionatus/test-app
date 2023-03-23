<?php

namespace Tests\Unit\Models\InternalNotification\Scopes;

use App\Models\InternalNotification;
use App\Models\InternalNotification\Scopes\Unread;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnreadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_read_status()
    {
        InternalNotification::factory()->count(10)->create();
        InternalNotification::factory()->read()->count(10)->create();

        $unreadInternalNotifications = InternalNotification::scoped(new Unread())->get();

        $this->assertCount(10, $unreadInternalNotifications);
    }
}
