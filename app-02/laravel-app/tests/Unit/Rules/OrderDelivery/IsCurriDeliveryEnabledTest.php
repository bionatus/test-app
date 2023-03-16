<?php

namespace Tests\Unit\Rules\OrderDelivery;

use App\Models\Supplier;
use App\Rules\OrderDelivery\IsCurriDeliveryEnabled;
use Tests\TestCase;

class IsCurriDeliveryEnabledTest extends TestCase
{
    /**
     * @test
     * @dataProvider enabledProvider
     */
    public function it_returns_true_if_the_supplier_is_curri_delivery_enabled($enabled)
    {
        $supplier = \Mockery::mock(Supplier::class);
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->once()->andReturn($enabled);

        $rule = new IsCurriDeliveryEnabled($supplier);

        $this->assertSame($enabled, $rule->passes('',''));
    }

    public function enabledProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /** @test */
    public function it_has_specific_message()
    {
        $supplier = new Supplier();
        $rule = new IsCurriDeliveryEnabled($supplier);

        $this->assertSame('The curri delivery is not enabled for this supplier.', $rule->message());
    }
}
