<?php

namespace Tests\Unit\Models;

use App\Models\PubnubChannel;
use App\Models\Supplier;
use App\Models\User;

class PubnubChannelTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(PubnubChannel::tableName(), [
            'id',
            'user_id',
            'supplier_id',
            'channel',
            'supplier_last_message_at',
            'user_last_message_at',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_uses_channel_as_route_key()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $pubnubChannel = PubnubChannel::factory()->usingSupplier($supplier)->create();

        $this->assertEquals($pubnubChannel->channel, $pubnubChannel->getRouteKey());
    }

    /** @test */
    public function it_fills_channel_on_creation()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $pubnubChannel = PubnubChannel::factory()->usingSupplier($supplier)->make(['channel' => null]);
        $pubnubChannel->save();

        $this->assertNotNull($pubnubChannel->channel);
    }

    /** @test */
    public function it_generates_channel_from_supplier_uuid_and_user_id()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $user          = User::factory()->create();
        $pubnubChannel = PubnubChannel::factory()
            ->usingSupplier($supplier)
            ->usingUser($user)
            ->make(['channel' => null]);
        $pubnubChannel->save();

        $supplierRouteKey = $pubnubChannel->supplier->getRouteKey();
        $userRouteKey     = $pubnubChannel->user->getRouteKey();
        $channel          = "supplier-$supplierRouteKey.user-$userRouteKey";

        $this->assertEquals($channel, $pubnubChannel->channel);
    }
}
