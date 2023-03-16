<?php

namespace Tests\Unit\Models;

use App\Models\PushNotificationToken;
use Illuminate\Support\Str;

class PushNotificationTokenTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(PushNotificationToken::tableName(), [
            'id',
            'uuid',
            'os',
            'device_id',
            'token',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_uses_uuid()
    {
        $pushNotificationToken = PushNotificationToken::factory()->create(['uuid' => Str::uuid()->toString()]);

        $this->assertEquals($pushNotificationToken->uuid, $pushNotificationToken->getRouteKey());
    }

    /** @test */
    public function it_fills_uuid_on_creation()
    {
        $pushNotificationToken = PushNotificationToken::factory()->make(['uuid' => null]);
        $pushNotificationToken->save();

        $this->assertNotNull($pushNotificationToken->uuid);
    }
}
