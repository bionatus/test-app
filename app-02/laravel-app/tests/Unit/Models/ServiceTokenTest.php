<?php

namespace Tests\Unit\Models;

use App\Models\ServiceToken;

class ServiceTokenTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(ServiceToken::tableName(), [
            'id',
            'service_name',
            'token_name',
            'value',
            'expired_at',
            'created_at',
            'updated_at',
        ]);
    }
}
