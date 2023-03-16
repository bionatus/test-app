<?php

namespace Tests\Unit\Models;

use App\Models\Agent;
use App\Models\Device;
use App\Models\PushNotificationToken;
use App\Models\Setting;
use App\Models\SettingUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AgentTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Agent::tableName(), [
            'id',
            'uuid',
        ]);
    }

    /** @test */
    public function it_uses_uuid()
    {
        $agent = Agent::factory()->create(['uuid' => Str::uuid()->toString()]);

        $this->assertEquals($agent->uuid, $agent->getRouteKey());
    }

    /** @test */
    public function it_fills_uuid_on_creation()
    {
        $agent = Agent::factory()->make(['uuid' => null]);
        $agent->save();

        $this->assertNotNull($agent->uuid);
    }

    /** @test */
    public function it_returns_fcm_tokens()
    {
        $agent                  = Agent::factory()->create();
        $devices                = Device::factory()->usingUser($agent->user)->count(2)->create();
        $pushNotificationTokens = Collection::make([]);
        $devices->each(function($device) use ($pushNotificationTokens) {
            $pushNotificationToken = PushNotificationToken::factory()->usingDevice($device)->create();
            $pushNotificationTokens->add($pushNotificationToken);
        });

        $this->assertEquals($agent->routeNotificationForFcm(), $pushNotificationTokens->pluck('token')->toArray());
    }

    /** @test */
    public function it_fails_to_set_its_availability_if_there_is_no_setting_for_that()
    {
        $agent = Agent::factory()->make();

        $this->assertNull($agent->setUnavailable());
    }

    /** @test */
    public function it_set_its_availability_if_setting_exists()
    {
        Setting::factory()->groupAgent()->create([Setting::routeKeyName() => Setting::SLUG_AGENT_AVAILABLE]);
        $agent = Agent::factory()->create();

        $this->assertInstanceOf(SettingUser::class, $settingUser = $agent->setUnavailable());
        $this->assertFalse($settingUser->value);
    }
}
