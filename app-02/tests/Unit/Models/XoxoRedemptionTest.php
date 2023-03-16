<?php

namespace Tests\Unit\Models;

use App\Models\XoxoRedemption;

class XoxoRedemptionTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(XoxoRedemption::tableName(), [
            'id',
            'uuid',
            'redemption_code',
            'voucher_code',
            'name',
            'image',
            'value_denomination',
            'amount_charged',
            'description',
            'instructions',
            'terms_conditions',
            'created_at',
            'updated_at',
        ]);
    }
}
