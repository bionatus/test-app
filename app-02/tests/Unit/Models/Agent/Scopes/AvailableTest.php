<?php

namespace Tests\Unit\Models\Agent\Scopes;

use App\Models\Agent;
use App\Models\Setting;
use App\Models\SettingUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filter_available()
    {
        $notAvailableUsers = SettingUser::factory()->count(2)->create()->pluck('user');
        Agent::factory()->usingUser($notAvailableUsers->first())->create();

        $noSettingsUsers = User::factory()->count(4)->create();
        Agent::factory()->usingUser($noSettingsUsers->first())->create();

        $setting = Setting::factory()->groupAgent()->boolean()->create(['slug' => Setting::SLUG_AGENT_AVAILABLE]);

        $availableUsers = SettingUser::factory()->usingSetting($setting)->count(3)->create([
            'value' => 1,
        ])->plucK('user');
        $availableUsers->take(2)->each(function(User $user) {
            Agent::factory()->usingUser($user)->create();
        });

        $this->assertCount(2, Agent::scoped(new Agent\Scopes\Available())->get());
    }
}
