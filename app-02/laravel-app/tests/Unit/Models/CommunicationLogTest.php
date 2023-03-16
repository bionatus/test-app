<?php

namespace Tests\Unit\Models;

use App\Models\CommunicationLog;

class CommunicationLogTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(CommunicationLog::tableName(), [
            'id',
            'communication_id',
            'description',
            'request',
            'response',
            'source',
            'errors',
            'created_at',
            'updated_at',
        ]);
    }
}
