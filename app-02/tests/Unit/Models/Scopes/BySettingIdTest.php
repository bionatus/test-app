<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Scopes\BySettingId;
use App\Models\Setting;
use App\Models\SettingUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BySettingIdTest extends TestCase
{
    use RefreshDatabase;

    private Setting $setting;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setting = Setting::factory()->create();
    }

    /** @test */
    public function it_filters_by_setting_id_on_setting_user_model()
    {
        SettingUser::factory()->count(2)->createQuietly();
        $expected = SettingUser::factory()->usingSetting($this->setting)->count(3)->createQuietly();

        $foundSettingUsers = SettingUser::scoped(new BySettingId($this->setting->getKey()))->get();

        $this->assertCount(3, $foundSettingUsers);
        $foundSettingUsers->each(function(SettingUser $settingUser) use ($expected) {
            $this->assertSame($expected->shift()->getKey(), $settingUser->getKey());
        });
    }
}
