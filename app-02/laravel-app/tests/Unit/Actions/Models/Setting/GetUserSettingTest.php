<?php

namespace Tests\Unit\Actions\Models\Setting;

use App\Actions\Models\Setting\GetUserSetting;
use App\Models\Setting;
use App\Models\SettingUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetUserSettingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_gets_setting_default_value()
    {
        $slugSms = Setting::SLUG_ORDER_IS_READY_FOR_APPROVAL_SMS;
        Setting::factory()->groupNotification()->applicableToUser()->create([
            'slug'  => $slugSms,
            'value' => true,
        ]);
        /** @var User $user */
        $user = User::factory()->createQuietly();

        $this->assertTrue((new GetUserSetting($user, $slugSms))->execute());
    }

    /** @test */
    public function it_gets_setting_users_value()
    {
        $slugSms = Setting::SLUG_ORDER_IS_READY_FOR_APPROVAL_SMS;
        $setting = Setting::factory()->groupNotification()->applicableToUser()->create([
            'slug'  => $slugSms,
            'value' => true,
        ]);
        /** @var User $user */
        $user = User::factory()->createQuietly();
        SettingUser::factory()->usingUser($user)->usingSetting($setting)->create(['value' => false]);

        $this->assertFalse((new GetUserSetting($user, $slugSms))->execute());
    }
}
