<?php

namespace Tests\Unit\Actions\Models\Setting;

use App\Actions\Models\Setting\GetStaffSetting;
use App\Models\Setting;
use App\Models\SettingStaff;
use App\Models\Staff;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetStaffSettingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_gets_setting_default_value()
    {
        $slugSms = Setting::SLUG_STAFF_SMS_NOTIFICATION;

        Setting::factory()->groupNotification()->boolean()->create([
            'slug'  => Setting::SLUG_STAFF_SMS_NOTIFICATION,
            'value' => true,
        ]);

        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->create();

        $this->assertTrue((new GetStaffSetting($staff, $slugSms))->execute());
    }

    /** @test */
    public function it_gets_setting_staffs_value()
    {
        $slugSms = Setting::SLUG_STAFF_SMS_NOTIFICATION;

        $setting = Setting::factory()->groupNotification()->boolean()->create([
            'slug'  => Setting::SLUG_STAFF_SMS_NOTIFICATION,
            'value' => true,
        ]);

        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->create();

        SettingStaff::factory()->usingStaff($staff)->usingSetting($setting)->create(['value' => false]);

        $this->assertFalse((new GetStaffSetting($staff, $slugSms))->execute());
    }
}
