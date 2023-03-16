<?php

namespace Tests\Unit\Models;

use App\Models\Setting;
use App\Models\SettingUser;

class SettingUserTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(SettingUser::tableName(), [
            'id',
            'user_id',
            'setting_id',
            'value',
            'created_at',
            'updated_at',
        ]);
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_cast_boolean_values(bool $expected, string $value)
    {
        $setting = Setting::factory()->boolean()->create();
        $SettingUser  = SettingUser::factory()->usingSetting($setting)->create(['value' => $value]);

        $this->assertSame($expected, $SettingUser->value);
    }

    public function dataProvider():array
    {
        return [
            [true, '1'],
            [true, 'true'],
            [true, 'false'],
            [false, '0'],
            [false, ''],
        ];
    }
}
