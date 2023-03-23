<?php

namespace Tests\Unit\Models;

use App\Models\Setting;
use App\Models\SettingSupplier;

class SettingSupplierTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(SettingSupplier::tableName(), [
            'id',
            'supplier_id',
            'setting_id',
            'value',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_cast_boolean_values()
    {
        $setting = Setting::factory()->boolean()->create();

        $trueSettingSupplier  = SettingSupplier::factory()->usingSetting($setting)->createQuietly(['value' => '1']);
        $falseSettingSupplier = SettingSupplier::factory()->usingSetting($setting)->createQuietly(['value' => '0']);

        $this->assertTrue($trueSettingSupplier->value);
        $this->assertFalse($falseSettingSupplier->value);
    }
}
