<?php

namespace Tests\Unit\Models;

use App\Models\XoxoVoucher;

class XoxoVoucherTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(XoxoVoucher::tableName(), [
            'id',
            'code',
            'name',
            'image',
            'value_denominations',
            'description',
            'instructions',
            'terms_conditions',
            'sort',
            'published_at',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_uses_code_as_route_key()
    {
        $xoxoVoucher = XoxoVoucher::factory()->create(['code' => 1234]);

        $this->assertEquals($xoxoVoucher->code, $xoxoVoucher->getRouteKey());
    }

    /** @test */
    public function it_gets_first_denomination()
    {
        $xoxoVoucher = XoxoVoucher::factory()->create(['value_denominations' => '20,30,40']);

        $this->assertEquals(20, $xoxoVoucher->first_denomination);
    }
}
