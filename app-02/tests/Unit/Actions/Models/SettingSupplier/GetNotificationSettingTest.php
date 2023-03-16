<?php

namespace Tests\Unit\Actions\Models\SettingSupplier;

use App\Actions\Models\SettingSupplier\GetNotificationSetting;
use App\Models\Setting;
use App\Models\SettingSupplier;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetNotificationSettingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_gets_notification_setting_default_value()
    {
        $slugSms = Setting::SLUG_NEW_ORDER_REQUEST_NOTIFICATION_SMS;
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => $slugSms,
            'value' => true,
        ]);
        $supplier = Supplier::factory()->createQuietly();

        $this->assertTrue((new GetNotificationSetting($supplier, $slugSms))->execute());
    }

    /** @test */
    public function it_gets_notification_setting_suppliers_value()
    {
        $slugSms  = Setting::SLUG_NEW_ORDER_REQUEST_NOTIFICATION_SMS;
        $setting  = Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => $slugSms,
            'value' => true,
        ]);
        $supplier = Supplier::factory()->createQuietly();
        SettingSupplier::factory()->usingSupplier($supplier)->usingSetting($setting)->create(['value' => false]);

        $this->assertFalse((new GetNotificationSetting($supplier, $slugSms))->execute());
    }
}
