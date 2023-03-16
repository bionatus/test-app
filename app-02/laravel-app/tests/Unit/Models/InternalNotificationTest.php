<?php

namespace Tests\Unit\Models;

use App\Models\InternalNotification;
use App\Models\User;
use Illuminate\Support\Str;

class InternalNotificationTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(InternalNotification::tablename(), [
            'id',
            'user_id',
            'uuid',
            'message',
            'read_at',
            'source_event',
            'source_type',
            'source_id',
            'data',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_uses_uuid()
    {
        $internalNotification = InternalNotification::factory()->create(['uuid' => Str::uuid()->toString()]);

        $this->assertEquals($internalNotification->uuid, $internalNotification->getRouteKey());
    }

    /** @test */
    public function it_fills_uuid_on_creation()
    {
        $internalNotification = InternalNotification::factory()->make(['uuid' => null]);
        $internalNotification->save();

        $this->assertNotNull($internalNotification->uuid);
    }

    /** @test */
    public function it_knows_if_it_has_been_read()
    {
        $unreadNotification = InternalNotification::factory()->create();
        $readNotification   = InternalNotification::factory()->read()->create();

        $this->assertTrue($readNotification->isRead());
        $this->assertFalse($unreadNotification->isRead());
    }

    /** @test */
    public function it_can_set_itself_as_read()
    {
        $internalNotification = InternalNotification::factory()->create();

        $this->assertFalse($internalNotification->isRead());

        $internalNotification->read();

        $this->assertTrue($internalNotification->isRead());
    }

    /** @test */
    public function it_knows_its_owner()
    {
        $internalNotification = InternalNotification::factory()->create();
        $otherUser            = User::factory()->create();

        $this->assertTrue($internalNotification->isOwner($internalNotification->user));
        $this->assertFalse($internalNotification->isOwner($otherUser));
    }
}
