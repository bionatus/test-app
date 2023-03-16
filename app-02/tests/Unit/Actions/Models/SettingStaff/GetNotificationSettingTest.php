<?php

namespace Tests\Unit\Actions\Models\SettingStaff;

use App\Actions\Models\SettingStaff\GetNotificationSetting;
use App\Models\Setting;
use App\Models\SettingStaff;
use App\Models\Staff;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetNotificationSettingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_gets_notification_setting_default_value()
    {
        $slugSms = Setting::SLUG_STAFF_SMS_NOTIFICATION;

        Setting::factory()->groupNotification()->boolean()->create([
            'slug'  => Setting::SLUG_STAFF_SMS_NOTIFICATION,
            'value' => 'false',
        ]);

        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->create();

        $this->assertTrue((new GetNotificationSetting($staff, $slugSms))->execute());
    }

    /** @test */
    public function it_gets_notification_setting_users_value()
    {
        $slugSms = Setting::SLUG_STAFF_SMS_NOTIFICATION;

        $setting = Setting::factory()->groupNotification()->boolean()->create([
            'slug'  => Setting::SLUG_STAFF_SMS_NOTIFICATION,
            'value' => 'false',
        ]);

        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->create();

        SettingStaff::factory()->usingStaff($staff)->usingSetting($setting)->create(['value' => false]);

        $this->assertFalse((new GetNotificationSetting($staff, $slugSms))->execute());
    }
}
