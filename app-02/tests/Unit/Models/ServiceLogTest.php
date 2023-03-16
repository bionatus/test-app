<?php

namespace Tests\Unit\Models;

use App\Models\ServiceLog;

class ServiceLogTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(ServiceLog::tableName(), [
            'id',
            'causer_id',
            'causer_type',
            'name',
            'request_method',
            'request_url',
            'request_payload',
            'response_status',
            'response_content',
            'created_at',
            'updated_at',
        ]);
    }
}

