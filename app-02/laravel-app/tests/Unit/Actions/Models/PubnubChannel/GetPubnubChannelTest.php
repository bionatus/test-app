<?php

namespace Tests\Unit\Actions\Models\PubnubChannel;

use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Models\PubnubChannel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetPubnubChannelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_new_pubnub_channel_when_it_does_not_exits()
    {
        $supplier = Supplier::factory()->createQuietly();
        $user     = User::factory()->create();

        $action = new GetPubnubChannel($supplier, $user);
        $action->execute();

        $this->assertDatabaseCount(PubnubChannel::tableName(), 1);
        $this->assertDatabaseHas(PubnubChannel::tableName(), [
            'user_id'     => $user->getKey(),
            'supplier_id' => $supplier->getKey(),
        ]);
    }

    /** @test */
    public function it_return_the_same_pubnub_channel_if_exist_someone()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $user          = User::factory()->create();
        $pubnubChannel = PubnubChannel::factory()->usingSupplier($supplier)->usingUser($user)->create();

        $action              = new GetPubnubChannel($supplier, $user);
        $returnPubnubChannel = $action->execute();

        $this->assertSame($returnPubnubChannel->getRouteKey(), $pubnubChannel->getRouteKey());
    }
}
