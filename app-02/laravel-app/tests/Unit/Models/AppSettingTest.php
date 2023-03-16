<?php

namespace Tests\Unit\Models;

use App\Models\AppSetting;

class AppSettingTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(AppSetting::tableName(), [
            'id',
            'slug',
            'label',
            'value',
            'type',
            'created_at',
            'updated_at',
        ]);
    }
}
