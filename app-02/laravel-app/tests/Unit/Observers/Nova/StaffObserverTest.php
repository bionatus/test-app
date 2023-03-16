<?php

namespace Tests\Unit\Observers\Nova;

use App\Models\Scopes\ByRouteKey;
use App\Models\Setting;
use App\Models\SettingStaff;
use App\Models\Staff;
use App\Models\Supplier;
use App\Nova\Observers\StaffObserver;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::factory()->create(['slug' => Setting::SLUG_STAFF_SMS_NOTIFICATION]);
        Setting::factory()->create(['slug' => Setting::SLUG_STAFF_EMAIL_NOTIFICATION]);
    }

    /** @test */
    public function it_creates_counter_staff_notification_settings_when_saved()
    {
        $supplier       = Supplier::factory()->createQuietly();
        $staff          = Staff::factory()->usingSupplier($supplier)->counter()->create();
        $settingSmsId   = Setting::scoped(new ByRouteKey(Setting::SLUG_STAFF_SMS_NOTIFICATION))->first()->getKey();
        $settingEmailId = Setting::scoped(new ByRouteKey(Setting::SLUG_STAFF_EMAIL_NOTIFICATION))->first()->getKey();

        request()->replace(['sms_notification' => true, 'email_notification' => true]);

        $observer = new StaffObserver();
        $observer->saved($staff);

        $this->assertDatabaseHas(SettingStaff::tableName(), ['setting_id' => $settingSmsId, 'value' => true]);
        $this->assertDatabaseHas(SettingStaff::tableName(), ['setting_id' => $settingEmailId, 'value' => true]);
    }

    /** @test */
    public function it_updates_counter_staff_notification_settings_when_saved_if_it_already_exists()
    {
        $supplier       = Supplier::factory()->createQuietly();
        $staff          = Staff::factory()->usingSupplier($supplier)->counter()->create();
        $settingSmsId   = Setting::scoped(new ByRouteKey(Setting::SLUG_STAFF_SMS_NOTIFICATION))->first()->getKey();
        $settingEmailId = Setting::scoped(new ByRouteKey(Setting::SLUG_STAFF_EMAIL_NOTIFICATION))->first()->getKey();
        $staff->settingStaffs()->create([
            'setting_id' => $settingSmsId,
            'value'      => true,
        ]);
        $staff->settingStaffs()->create([
            'setting_id' => $settingEmailId,
            'value'      => true,
        ]);

        request()->replace(['sms_notification' => false, 'email_notification' => false]);

        $observer = new StaffObserver();
        $observer->saved($staff);

        $this->assertDatabaseHas(SettingStaff::tableName(), ['setting_id' => $settingSmsId, 'value' => false]);
        $this->assertDatabaseHas(SettingStaff::tableName(), ['setting_id' => $settingEmailId, 'value' => false]);
    }

    /** @test */
    public function it_does_not_creates_staff_notification_setting_if_not_present_ion_the_request()
    {
        $supplier       = Supplier::factory()->createQuietly();
        $staff          = Staff::factory()->usingSupplier($supplier)->counter()->create();
        $settingSmsId   = Setting::scoped(new ByRouteKey(Setting::SLUG_STAFF_SMS_NOTIFICATION))->first()->getKey();
        $settingEmailId = Setting::scoped(new ByRouteKey(Setting::SLUG_STAFF_EMAIL_NOTIFICATION))->first()->getKey();

        $observer = new StaffObserver();
        $observer->saved($staff);

        $this->assertDatabaseMissing(SettingStaff::tableName(), [
            'setting_id' => $settingSmsId,
            'staff_id'   => $staff->getKey(),
        ]);
        $this->assertDatabaseMissing(SettingStaff::tableName(), [
            'setting_id' => $settingEmailId,
            'staff_id'   => $staff->getKey(),
        ]);
    }

    /** @test */
    public function it_does_not_updates_staff_notification_setting_if_not_present_ion_the_request()
    {
        $supplier       = Supplier::factory()->createQuietly();
        $staff          = Staff::factory()->usingSupplier($supplier)->counter()->create();
        $settingSmsId   = Setting::scoped(new ByRouteKey(Setting::SLUG_STAFF_SMS_NOTIFICATION))->first()->getKey();
        $settingEmailId = Setting::scoped(new ByRouteKey(Setting::SLUG_STAFF_EMAIL_NOTIFICATION))->first()->getKey();
        $dateTime       = CarbonImmutable::now();
        $staff->settingStaffs()->create([
            'setting_id' => $settingSmsId,
            'value'      => true,
            'updated_at' => $dateTime->subMinute(),
        ]);
        $staff->settingStaffs()->create([
            'setting_id' => $settingEmailId,
            'value'      => true,
            'updated_at' => $dateTime->subMinute(),
        ]);

        $observer = new StaffObserver();
        $observer->saved($staff);

        $this->assertDatabaseHas(SettingStaff::tableName(), [
            'setting_id' => $settingEmailId,
            'staff_id'   => $staff->getKey(),
            'value'      => true,
            'updated_at' => $dateTime->subMinute(),
        ]);
        $this->assertDatabaseHas(SettingStaff::tableName(), [
            'setting_id' => $settingSmsId,
            'staff_id'   => $staff->getKey(),
            'value'      => true,
            'updated_at' => $dateTime->subMinute(),
        ]);
    }
}
