<?php

namespace Tests\Unit\Actions\Models\SettingUser;

use App\Actions\Models\SettingUser\GetNotificationSetting;
use App\Models\Setting;
use App\Models\SettingUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetNotificationSettingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_gets_notification_setting_default_value()
    {
        $slugSms = Setting::SLUG_ORDER_IS_READY_FOR_APPROVAL_SMS;
        Setting::factory()->groupNotification()->applicableToUser()->create([
            'slug'  => $slugSms,
            'value' => true,
        ]);
        /** @var User $user */
        $user = User::factory()->createQuietly();

        $this->assertTrue((new GetNotificationSetting($user, $slugSms))->execute());
    }

    /** @test */
    public function it_gets_notification_setting_users_value()
    {
        $slugSms = Setting::SLUG_ORDER_IS_READY_FOR_APPROVAL_SMS;
        $setting = Setting::factory()->groupNotification()->applicableToUser()->create([
            'slug'  => $slugSms,
            'value' => true,
        ]);
        /** @var User $user */
        $user    = User::factory()->createQuietly();
        SettingUser::factory()->usingUser($user)->usingSetting($setting)->create(['value' => false]);

        $this->assertFalse((new GetNotificationSetting($user, $slugSms))->execute());
    }
}
