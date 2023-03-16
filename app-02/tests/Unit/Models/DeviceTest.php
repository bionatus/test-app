<?php

namespace Tests\Unit\Models;

use App\Models\Device;

class DeviceTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Device::tableName(), [
            'id',
            'udid',
            'app_version',
            'user_id',
            'token',
            'created_at',
            'updated_at',
        ]);
    }
}
