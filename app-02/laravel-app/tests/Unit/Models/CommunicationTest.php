<?php

namespace Tests\Unit\Models;

use App\Models\Call;
use App\Models\Communication;
use Illuminate\Support\Str;

class CommunicationTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Communication::tableName(), [
            'id',
            'session_id',
            'uuid',
            'provider',
            'provider_id',
            'channel',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_knows_if_is_of_call_channel_and_has_a_db_call_record_related()
    {
        $noCallChannel    = Communication::factory()->chat()->create();
        $callChannelNoDB  = Communication::factory()->call()->create();
        $callChannelAndDB = Call::factory()->create()->communication;

        $this->assertFalse($noCallChannel->isCall());
        $this->assertFalse($callChannelNoDB->isCall());
        $this->assertTrue($callChannelAndDB->isCall());
    }

    /** @test */
    public function it_uses_uuid()
    {
        $communication = Communication::factory()->create(['uuid' => Str::uuid()->toString()]);

        $this->assertEquals($communication->uuid, $communication->getRouteKey());
    }

    /** @test */
    public function it_fills_uuid_on_creation()
    {
        $communication = Communication::factory()->make(['uuid' => null]);
        $communication->save();

        $this->assertNotNull($communication->uuid);
    }
}
