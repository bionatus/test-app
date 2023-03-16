<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Scopes\AlphabeticallyWithNullLast;
use App\Models\Supply;
use App\Models\XoxoVoucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class AlphabeticallyWithNullLastTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_supply_sort_with_null_last()
    {
        $scopeField   = 'sort';
        $fourthSupply = Supply::factory()->create();
        $secondSupply = Supply::factory()->sort(2)->create();
        $thirdSupply  = Supply::factory()->sort(3)->create();
        $firstSupply  = Supply::factory()->sort(1)->create();

        $expectedSupplies = Collection::make([
            $firstSupply,
            $secondSupply,
            $thirdSupply,
            $fourthSupply,
        ]);

        $orderedSupplies = Supply::scoped(new AlphabeticallyWithNullLast($scopeField))->get();

        $orderedSupplies->each(function(Supply $supply) use ($expectedSupplies) {
            $this->assertSame($expectedSupplies->shift()->getKey(), $supply->getKey());
        });
    }

    /** @test */
    public function it_xoxo_vouchers_sort_with_null_last()
    {
        $scopeField   = 'sort';
        $fourthSupply = XoxoVoucher::factory()->create();
        $secondSupply = XoxoVoucher::factory()->sort(2)->create();
        $thirdSupply  = XoxoVoucher::factory()->sort(3)->create();
        $firstSupply  = XoxoVoucher::factory()->sort(1)->create();

        $expectedXoxoVouchers = Collection::make([
            $firstSupply,
            $secondSupply,
            $thirdSupply,
            $fourthSupply,
        ]);

        $orderedXoxoVouchers = XoxoVoucher::scoped(new AlphabeticallyWithNullLast($scopeField))->get();

        $orderedXoxoVouchers->each(function(XoxoVoucher $xoxoVoucherItem) use ($expectedXoxoVouchers) {
            $this->assertSame($expectedXoxoVouchers->shift()->getKey(), $xoxoVoucherItem->getKey());
        });
    }
}
